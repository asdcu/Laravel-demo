<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * 创建问题
     * @return array
     */
    public function add(){
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in()){
            return ['status' => FALSE, 'msg' => 'login required'];
        }

        /*检查是否传入标题*/
        if(!rq('title')){
            return ['status'=> FALSE, 'msg' => 'title required'];
        }

        $this->title = rq('title');
        $this->user_id = session('user_id');
        if(rq('desc')){
            $this->desc = rq('desc');
        }

        /*写入数据库*/
        return $this->save() ? ['status' => TRUE, 'id' => $this->id] : ['status' => FALSE, 'msg' => 'db insert failed'];
    }

    /**
     * 更新问题
     * @return array
     */
    public function change()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in()){
            return ['status' => FALSE, 'msg' => 'login required'];
        }

        /*检查问题ID*/
        if(!rq('id')){
            return ['status' => FALSE, 'msg' => "question's id required"];
        }

        /*根据本ID获取问题Model*/
        $question = $this->find(rq('id'));

        if(!$question)
            return ['status' => FALSE, 'msg' => 'question not exists'];

        if($question->user_id != session('user_id')){
            return ['status' => FALSE, 'msg' => 'permission denied'];
        }
        if(rq('title'))
            $question->title = rq('title');
        if(rq('desc'))
            $question->desc = rq('desc');
        /*写入数据库*/
        return $question->save() ? ['status' => TRUE, 'msg' => 'update success'] : ['status' => FALSE, 'msg' => 'update db failed'];
    }

    public function read()
    {
        /*判断是否传入问题ID，有问题ID，则是读取具体问题，否则读取所有ID*/
        if(rq('id'))
            return ['status' => TRUE, 'data' => $this->find(rq('id'))];

        /*分页相关参数*/
        list($limit, $skip) = paginate(rq('page'), rq('limit'));

        /*获取问题collection*/
        $questions = $this
            ->orderBy('created_at')
            ->limit($limit)
            ->skip($skip)
            ->get(['id','title','desc','created_at','updated_at','user_id'])
            ->keyBy('id');
        if($questions)
            return ['status' => TRUE, 'data' => $questions];
        return ['status' => FALSE, 'data' => 'no questions found'];
    }

    public function remove()
    {
        /*检查用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status' => 0, 'msg' => 'login required'];

        /*判断是否有传入id参数*/
        if(!rq('id'))
            return ['status' => FALSE, 'msg' => "question's id required"];

        /*获取question的collection*/
        $question = $this->find(rq('id'));
        if(!$question)
            return ['status' => FALSE, 'msg' => 'question not exists'];

        /*检查当前用户是否是问题的所有者*/
        if($question->user_id != session('user_id'))
            return ['status' => FALSE, 'msg' => 'permission denied'];

        /*从数据库中删除question*/
        return $question->delete() ? ['status' => TRUE, 'msg' => 'delete success'] : ['status' => FALSE, 'msg' => 'db failed'];
    }
}
