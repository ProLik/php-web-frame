<?php

/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 14:43
 */
class Demo_IndexController extends Demo_BaseController
{
    public function index($params, $request){
        $request->set_attribute("msg", "The PHP WEB Frame is correct running!");
        return "Demo_Index";
    }


    public function get_json(){
        return array(
            "key"=>"test",
            "value"=>"1",
            "msg"=>"success"
        );
    }

}