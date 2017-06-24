<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Request;

class   User extends Model
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
        $hashed_password = $user->password;
    }

    /**
     * 检测用户名和密码是否存在
     * @return array
     */
    public function has_username_and_password(){
        $username = Request::get('username');
        $password = Request::get('password');
        return $username && $password ? array('username'=>$username,'password'=>$password) : array();
    }
}
