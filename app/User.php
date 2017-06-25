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
                'msg' => 'user exists'
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
            'msg' => 'registe failed'
        ];
    }

    public function read(){
        if(!rq('id'))
            return ['status' => FALSE, 'msg' => 'id required'];
        $get = array('id','username', 'avatar_url', 'intro');
        $user = $this->find(rq('id'), $get);
        $data = $user->toArray();
        if(!$user)
            return ['status' => FALSE, 'msg' => 'user not exists'];
        $answer_count = answer_ins()->where('user_id', rq('id'))->count();
        $question_count = question_ins()->where('user_id', rq('id'))->count();
        $data['answer_count'] = $answer_count;
        $data['question_count'] = $question_count;
        return ['status' => TRUE, 'data' => $data];
    }

    public function login(){
        $has_username_and_password = $this->has_username_and_password();
        if(!$has_username_and_password){
            return ['status' => FALSE, 'msg' => 'username and password are required'];
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
            return ['status' => FALSE, 'msg' => 'user not exists'];
        }

        /**
         * 检测密码是否正确
         */
        $hashed_password = $user->password;
        if(!Hash::check($password, $hashed_password)){
            return ['status' => FALSE, 'msg' => 'invalid password'];
        }

        /**
         * 写入session
         */
        session()->put('username', $username);
        session()->put('user_id', $user->id);

        return ['status' => TRUE, 'msg' => 'login successfully', 'data' => array('id' => $user->id)];
    }

    public function logout(){
//        session()->flush(); //清除所有session,虽然这里可以这样实现，但是在负责的项目中需求可能不一样
//        $username = session()->pull('username'); //将session中的内容直接取出变量，并清空当前健值的session
//        session()->set('perspon.username', 'asd'); //session可嵌套
        if(!session('username') && !session('user_id'))
            return ['status' => FALSE, 'msg' => 'still not login'];
        session()->forget('username');
        session()->forget('user_id');
        return ['status' => TRUE, 'msg' => 'logout successfully'];
    }

    public function change_password(){
        if(!$this->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        if(!rq('old_password') ||!rq('new_password'))
            return ['status' => FALSE, 'msg' => 'old_password and new_password are required'];

        $user = $this->find(session('user_id'));

        if(!Hash::check(rq('old_password'), $user->password))
            return ['status' => FALSE, 'msg' => 'invalid old password'];

        $user->password = bcrypt(rq('new_password'));
        return $user->save() ? ['status' => TRUE, 'msg' => 'edit successfully'] : ['status' => FALSE, 'msg' => 'db update failed'];
    }

    public function reset_password(){
        if(!rq('phone'))
            return ['status' => FALSE, 'msg' => 'phone required'];

        $user = $this->where('phone', rq('phone'))->first();
        if(!$user)
            return ['status' => FALSE, 'msg' => 'invalid phone number'];
        $captcha = $this->generate_captcha();
        $user->phone_captcha = $captcha;
        return $user->save() ? ['status' => TRUE, 'msg' => 'edit successfully'] : ['status' => FALSE, 'msg' => 'db update failed'];
    }

    /**
     * 随机生成验证码
     * @return int
     */
    private function generate_captcha(){
        return rand(1000,9999);
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

    public function answers(){
        return $this->belongsToMany('App\Answer')
            ->withPivot('vote')
            ->withTimestamps();
    }

    public function questions(){
        return $this->belongsToMany('App\Question')
            ->withPivot('vote')
            ->withTimestamps();
    }
}
