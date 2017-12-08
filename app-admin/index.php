<?php

//设置应该报告何种 PHP 错误
//报告所有 PHP 错误
//运行时不影响的错误
error_reporting(E_ALL & ~E_NOTICE);
//dirname(dirname(__FILE__));得到的是文件上一层目录名
//dirname(__FILE__);得到的是文件所在层目录名
define(ROOT_PATH, dirname(dirname(__FILE__))."/");
//   ./ 当前目录
//   ../ 父级目录
//   / 根目录
define(SYS_PATH, '../system');

define(CUR_PATH, ROOT_PATH . 'app-admin');

require_once(SYS_PATH . "/functions.php");

//引入文件路径
$INCLUDE_PATH = array(
	CUR_PATH,
	ROOT_PATH.'app-admin',
	ROOT_PATH.'app-shuidi',
	ROOT_PATH.'app-core'
);

//读取配置文件路径
$CONFIG_PATH = array(
	CUR_PATH.'/config',
	ROOT_PATH.'app-core/config',
	ROOT_PATH.'config',
);

cf_require_class('RSF');
cf_require_class('AdminRequest');
cf_require_class('AdminResponse');
DPS::get_instance()->set_request(new Request());
DPS::get_instance()->set_response(new Response());
DPS::get_instance()->run();
