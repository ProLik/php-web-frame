<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/4/30
 * Time: 11:20
 */

error_reporting(E_ALL & ~E_NOTICE);

error_reporting(E_ALL & ~E_NOTICE);
//dirname(dirname(__FILE__));得到的是文件上一层目录名
//dirname(__FILE__);得到的是文件所在层目录名
define("ROOT_PATH", dirname(dirname(__FILE__))."/");
//   ./ 当前目录
//   ../ 父级目录
//   / 根目录
define("SYS_PATH", '../system');

define("CUR_PATH", ROOT_PATH . 'app-demo');

require_once(SYS_PATH. "/functions.php");

$INCLUDE_PATH = array(
    CUR_PATH,
    ROOT_PATH . 'app-core'
);

$CONFIG_PATH = array(
    ROOT_PATH . 'app-core/config',
    CUR_PATH . '/config',
    ROOT_PATH . 'config'
);



set_time_limit(ConfigTool::get_instance()->get_config("time_out"));
/*
cf_require_class("DemoRequest");*/

DPS::get_instance()->get_request(new DemoRequest());
DPS::get_instance()->run();


