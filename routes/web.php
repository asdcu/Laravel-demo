<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

function rq($key=null, $default=null){
    if(!$key)
        return Request::all();
    return Request::get($key, $default);
}

function user_ins(){
    return new App\User();
}

function question_ins(){
    return new App\Question();
}

function answer_ins(){
    return new App\Answer();
}

Route::get('/', function () {
    return view('welcome');
});


Route::get('api', function (){
    return ['version' => 0.1];
});

/**
 * 注册
 */
Route::any('api/signup', function(){
    return user_ins()->signup();
});

/**
 * 登录
 */
Route::any('api/login', function (){
    return user_ins()->login();
});

/**
 * 登出
 */
Route::any('api/logout', function (){
    return user_ins()->logout();
});

/**
 * 增加问题
 */
Route::any('api/question/add', function(){
    return question_ins()->add();
});

/**
 * 修改问题
 */
Route::any('api/question/change', function(){
   return question_ins()->change();
});

/**
 * 查看问题
 */
Route::any('api/question/read', function(){
    return question_ins()->read();
});

/**
 * 删除问题
 */
Route::any('api/question/remove', function(){
    return question_ins()->remove();
});

/**
 * 增加回答
 */
Route::any('api/answer/add', function(){
    return answer_ins()->add();
});

/**
 * 修改回答
 */
Route::any('api/answer/change', function(){
    return answer_ins()->change();
});

/**
 * 查看回答
 */
Route::any('api/answer/read', function(){
    return answer_ins()->read();
});

/**
 * 测试方法
 */
Route::any('test', function(){
    /**
     * 这里实现测试的逻辑
     */
    dd(user_ins()->is_logged_in());
});

