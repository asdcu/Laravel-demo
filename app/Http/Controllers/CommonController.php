<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommonController extends Controller
{
    /**
     * 时间先Api
     * @return mixed
     */
    public function timeline(){
        list($limit, $skip) = paginate(rq('page'), rq('limit'));

        /*获取问题数据*/
        $questions = question_ins()->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        /*获取回答数据*/
        $answers = answer_ins()->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        /*合并数据*/
        $data = $questions->merge($answers);

        /*将合并的数据按时间进行排序*/
        $data = $data->sortBy(function ($item){
           return $item->created_at;
        });
        $data = $data->values()->all();
        if($data)
            return ['status' => TRUE, 'data'=>$data];
        return ['status' => FALSE, 'msg' => 'no data exists'];
    }
}
