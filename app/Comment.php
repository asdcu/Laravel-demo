<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function add()
    {
        if(!user_ins()->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        if(!rq('content'))
            return ['status' => FALSE, 'msg' => 'empty content'];

        if((!rq('question_id') && ! rq('answer_id')) || (rq('question_id') && rq('answer_id')))
            return ['status' => FALSE, 'msg' => 'params error'];

        /*评论问题*/
        if(rq('question_id')){
            $question = question_ins()->find(rq('question_id'));
            if(!$question)
                return ['status' => FALSE, 'msg' => 'question not exists'];
            $this->question_id = rq('question_id');
        }else{
            /*评论答案*/
            $answer = answer_ins()->find(rq('answer_id'));
            if(!$answer)
                return ['status' => FALSE, 'msg' => 'answer not exists'];
            $this->answer_id = rq('answer_id');
        }
        /*检查是否评论回复*/
        if(rq('reply_to')){
            $target = $this->find(rq('reply_to'));
            /*检查是否存在目标*/
            if(!$target)
                return ['status' => FALSE, 'msg' => 'target not exists'];
            /*检查是否回复自己的评论*/
            if($target->user_id == session('user_id'))
                return ['status' => FALSE, 'msg' => 'can not reply to yourself' ];
            $this->reply_to = rq('reply_to');
        }
        $this->content = rq('content');
        $this->user_id = session('user_id');
        return $this->save() ? ['status' => TRUE, 'data' => array('id' => $this->id)] : ['status' => FALSE, 'msg' => 'db insert failed'];
    }

    /**
     * 查看评论
     * @return array
     */
    public function read()
    {
        /*判断是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        /*判断有没有传参数*/
        if(!(rq('question_id') || rq('answer_id')))
            return ['status' => FALSE, 'msg' => 'question_id or answer_id is required'];

        /*判断是否读取问题评论还是评论的回复评论*/
        if(rq('question_id')){
            $question = question_ins()->find(rq('question_id'));
            if(!$question)
                return ['status' => FALSE, 'msg' => 'question not exists'];
            $data = $this->where('question_id', rq('question_id'))->get();
        }else{
            $answer = answer_ins()->find(rq('answer_id'));
            if(!$answer)
                return ['status' => FALSE, 'msg' => 'answer not exists'];
            $data = $this->where('answer_id', rq('answer_id'))->get();
        }
        $data = $data->keyBy('id');
        return ['status' => TRUE, 'data' => $data];
    }

    public function remove()
    {
        if(!user_ins()->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        if(!rq('id'))
            return ['status' => FALSE, 'msg' => 'comment_id required'];

        $comment = $this->find(rq('id'));
        if(!$comment)
            return ['status' => FALSE, 'msg' => 'comment not exists'];

        if($comment->user_id != session('user_id'))
            return ['status' => FALSE, 'msg' => 'permission denied'];

        /*删除关联表数据*/
        $this->where('reply_to', rq('id'))->delete();
        return $comment->delete() ? ['status' => TRUE, 'msg' => '删除成功'] : ['status' => FALSE, 'msg' => 'db delete failed'];

    }
}
