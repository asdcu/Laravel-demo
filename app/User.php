<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Request;

class User extends Model
{
    public function signup(){

        /**
         * 检验非空字段是否为空
         */
        $check = $this->has_username_and_password();
        if(!$check){
            return ['status' => FALSE, 'msg' => '用户名或密码不能为空'];
        }

        /**
         * 获取用户名和密码
         */
        $username = $check['username'];
        $password = $check['password'];

        /**
         * 检验用户是否已存在
         */
        $user_exists = $this->where('username', $username)->exists();
        if($user_exists){
            return [
                'status' => FALSE,
                'msg' => '用户已存在'
            ];
        }
//        $hashed_password = Hash::make($password);
        $hashed_password = bcrypt($password);
        $user = $this;
        $user->username = $username;
        $user->password = $hashed_password;
        if($user->save()){
            return [
                'status' => TRUE,
                'data' => array(
                    'id' => $user->id
                )
            ];
        }
        return [
            'status' => FALSE,
            'msg' => '注册失败'
        ];
    }

    public function login(){
        $has_username_and_password = $this->has_username_and_password();
        if(!$has_username_and_password){
            return ['status' => FALSE, 'msg' => '用户名或密码不能为空'];
        }

        /**
         * 获取用户名和密码
         */
        $username = $has_username_and_password['username'];
        $password = $has_username_and_password['password'];

        /**
         * 检查用户是否存在
         */
        $user = $this->where('username', $username)->first();
        if(!$user){
            return ['status' => FALSE, 'msg' => '用户不存在'];
        }

        /**
         * 检测密码是否正确
         */
        $hashed_password = $user->password;
        if(!Hash::check($password, $hashed_password)){
            return ['status' => FALSE, 'msg' => '密码有误'];
        }

        /**
         * 写入session
         */
        session()->put('username', $username);
        session()->put('user_id', $user->id);

        return ['status' => TRUE, 'msg' => '登录成功', 'data' => array('id' => $user->id)];
    }

    public function logout(){
//        session()->flush(); //清除所有session,虽然这里可以这样实现，但是在负责的项目中需求可能不一样
//        $username = session()->pull('username'); //将session中的内容直接取出变量，并清空当前健值的session
//        session()->set('perspon.username', 'asd'); //session可嵌套
        if(!session('username') && !session('user_id'))
            return ['status' => FALSE, 'msg' => '暂时还没登录'];
        session()->forget('username');
        session()->forget('user_id');
        return ['status' => TRUE, 'msg' => '退出成功'];
    }

    /**
     * 检测用户名和密码是否存在
     * @return array
     */
    public function has_username_and_password(){
        $username = rq('username');
        $password = rq('password');
        return $username && $password ? array('username'=>$username,'password'=>$password) : array();
    }

    /**
     * 判断用户是否登录
     * @return bool
     */
    public function is_logged_in(){
        return session('user_id') ?: false;
    }
}
