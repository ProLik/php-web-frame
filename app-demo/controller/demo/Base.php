<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 14:48
 */
cf_require_controller("ActionController");
class Demo_BaseController extends ActionController
{


    public function success($data = [], $msg = '请求成功!') {
        $data = $this->format_value($data);
        return array(
            'status' => 0,
            'message' => $msg,
            'data' => $data,
        );
    }

    private function format_value(&$arr) {
        if (empty($arr)) {
            // 防止空数组变成空串
            $arr = array();
        }
        foreach ($arr as $key => &$val) {
            if (is_null($val) || strtolower($val) == 'null') {
                $val = '';
            }
            if ($val === true) {
                $val = '1';
            }

            if ($val === false) {
                $val = '0';
            }
            if (is_array($val)) {
                $this->format_value($val); // 递归调用
            }
        }
        return $arr;
    }

    public function error($status = 1, $message = '', $data = []) {
        //$data = $this->format_value($data);
        return array(
            'status' => $status,
            'message' => $message ? $message : $this->get_status_message($status),
            'data' => $data,
        );
    }

    private function get_status_message($status) {
        $code_message_list = array(
            0 => '请求成功',
            1 => '请求失败',
            2 => '参数不完整',
            3 => '无结果',
            4 => '参数不合法'
        );
        if (isset($code_message_list[$status])) {
            return $code_message_list[$status];
        } else {
            return $code_message_list[1];
        }
    }

}