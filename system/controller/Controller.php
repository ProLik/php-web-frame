<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/18
 * Time: 17:58
 */

abstract class Controller
{
    abstract public function run();


    public function router_action($handle = "action")
    {
        $request = DPS::get_instance()->get_request();
        $params = $request->get_params();
        $action = $params[$handle];
        $action = $action ? $action : "index";
        if(method_exists($this, $action)){
            $result = $this->$action($params, $request);
        }
        return $result;
    }


    public function auto_router($handle = "action")
    {
        $result = $this->router_action($handle);
        if(is_array($result)){
            echo json_encode($result);
            return false;
        }else{
            return $result;
        }
    }

}