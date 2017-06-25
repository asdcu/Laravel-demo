<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    /**
     * 添加回答
     * @return array
     */
    public function add()
    {
        /*判断用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        /*判断是否传入问题id和评论内容*/
        if(!rq('question_id') || !rq('content')){
            return ['status' => FALSE , 'msg' => 'question_id and content are both required'];
        }

        /*获取question的collection*/
        $question = question_ins()->find(rq('question_id'));
        if(!$question)
            return ['status' => FALSE, 'msg' => 'question not exists'];

        /*判断是否重复回答*/
        $answered = $this
            ->where(['question_id' => rq('question_id'), 'user_id' => session('user_id')])
            ->count();
        if($answered)
            return  ['status' => FALSE, 'msg' => 'duplicate answer'];

        $this->content = rq('content');
        $this->question_id = rq('question_id');
        $this->user_id = session('user_id');
        /*写入数据库*/
        return $this->save() ? ['status' => TRUE, 'data' => array('id' => $this->id)] : ['status' => FALSE, 'msg' => 'db insert failed'];
    }

    /**
     * 修改回答
     * @return array
     */
    public function change()
    {
        /*判断用户是否登录*/
        if(!user_ins()->is_logged_in())
            return ['status' => FALSE, 'msg' => 'login required'];

        /*判断是否传入id和content*/
        if(!rq('id') || !rq('content'))
            return ['status' => FALSE, 'msg' => "answer's id and content are both  required"];

        /*获取answer的collection*/
        $answer = $this->find(rq('id'));
        if(!$answer)
            return ['status' => TRUE, 'msg' => 'answer not exists'];

        /*判断是否具有修改权限*/
        if($answer->user_id != session('user_id'))
            return ['status' => FALSE, 'msg' => 'permissison denied'];
        $answer->content = rq('content');

        /*写入数据库*/
        return $answer->save() ? ['status' => TRUE, 'msg' => 'update success'] : ['status' => FALSE, 'msg' => 'db update failed'];
    }

    /**
     * 查看回答
     * @return array
     */
    public function read()
    {
        if(!rq('id') && !rq('question_id'))
            return ['status' => FALSE, 'msg' => 'answer_id or question_id is required'];

        /*获取特定id*/
        if(rq('id')){
            $answer = $this->find(rq('id'));
            if(!$answer)
                return ['status' => FALSE, 'msg' => 'answer not exists'];

            return ['status' => TRUE, 'data' => $answer];
        }
        /****************************查看同一问题下的所有回答*******************************/
        /*检查问题是否存在*/
        if(!question_ins()->find(rq('question_id')))
            return ['status' => TRUE, 'msg' => 'question not exists'];

        $answers = $this
            ->where('question_id', rq('question_id'))
            ->get()
            ->keyBy('id');

        return ['status' => TRUE, 'data' => $answers ];
    }
}