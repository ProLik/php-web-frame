<?php
/**
 * Created by PhpStorm.
 * User: LIKANG
 * Date: 2017/12/18
 * Time: 17:54
 */

abstract class Interceptor
{

    abstract public function go_next();

    public function broken(){
        DPS::get_instance()->get_response()->status_500();
        exit;
    }

}