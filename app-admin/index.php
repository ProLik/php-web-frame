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

$INCLUDE_PATH = array(
	CUR_PATH,
	ROOT_PATH.'app-admin',
	ROOT_PATH.'app-shuidi',
	ROOT_PATH.'app-core'
);

$CONFIG_PATH = array(
	CUR_PATH.'/config',
	ROOT_PATH.'app-core/config',
	ROOT_PATH.'config',
);

rsf_require_class('RSF');
rsf_require_class('AdminRequest');
rsf_require_class('AdminResponse');
RSF::get_instance()->setRequest(new AdminRequest());
RSF::get_instance()->setResponse(new AdminResponse());
RSF::get_instance()->run();
