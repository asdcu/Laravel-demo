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

function paginate($page=1, $limit=15){
    $limit = $limit ?: 15;
    $skip = ($page ? $page - 1 : 0) * $limit;
    return [$limit, $skip];
}

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

function comment_ins(){
    return new App\Comment();
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
 * 修改密码
 */
Route::any('api/user/change_password', function (){
    return user_ins()->change_password();
});

/**
 * 找回密码
 */
Route::any('api/user/reset_password', function (){
    return user_ins()->reset_password();
});

Route::any('api/user/read', function (){
    return user_ins()->read();
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
 * 投票
 */
Route::any('api/answer/vote', function(){
    return answer_ins()->vote();
});

/**
 * 增加评论
 */
Route::any('api/comment/add', function(){
    return comment_ins()->add();
});

/**
 * 查看评论
 */
Route::any('api/comment/read', function(){
    return comment_ins()->read();
});

/**
 * 删除评论
 */
Route::any('api/comment/remove', function(){
    return comment_ins()->remove();
});

Route::any('api/timeline', 'CommonController@timeline');



/**
 * 测试方法
 */
Route::any('test', function(){
    /**
     * 这里实现测试的逻辑
     */
    dd(user_ins()->is_logged_in());
});

