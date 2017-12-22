<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/22
 * Time: 16:46
 */

class ActionController extends Controller
{

    public function run()
    {
        $result = $this->router_action();
        if(is_array($result)){
            $response = DPS::get_instance()->get_response();
            $response->header("Content-Type","application/json; charset=utf-8");
            echo json_encode($result);
        }else{
            return $result;
        }
    }


    public function error($status = 1,$msg = '请求失败!'){
        $res = array(
            'status' => $status,
            'message' => $msg,
            'data' => [],
        );
        return $res;
    }

    public function success($data = [],$msg = '请求成功!'){
        $res = array(
            'status' => 0,
            'message' => $msg,
            'data' => $data,
        );
        return $res;
    }

}