<?php

$config['send_sms'] = array(
    'api_url' => 'http://www.baidu.com',
    'sp_code' => '2008',
    'login_name' => 'admin',
    'password' => '123456',
);

#1 开启缓存,0 关闭缓存
#$config['cache'] = 1;
$config['memcache'] = '11:11:11:11:111111';

$config['redis'] = array(
    'master'=>array('host' => '11:11:11:11', 'port' => 6379, 'password' => ''),
    'slave'=>array('host' => '11:11:11:12', 'port' => 6379, 'password' => '')
);