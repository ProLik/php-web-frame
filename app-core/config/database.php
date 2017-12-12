<?php
/**
 * Created by PhpStorm.
 * User: reco
 * Date: 2017/5/8
 * Time: 下午3:41
 */
$config['memcache'] = '11:11:11:11:111111';

$config['redis'] = array(
    'master'=>array('host' => '11:11:11:11', 'port' => 6379, 'password' => ''),
    'slave'=>array('host' => '11:11:11:12', 'port' => 6379, 'password' => '')
);

$config['redis_cluster'] = array(
    '11:11:11:22',
    '11:11:11:23',
    '11:11:11:24'
);

$config['solr'] = array(
    'host'=>'solr.test.com',
    'db'=>'db',
    'port'=>80
);

$config['mongodb']['ic'] = array(
    'dbname'=>'test1',
    'uri'=>'mongodb://test:123456@11:11:11:33/admin'
);
$config['mongodb']['ucs'] = array(
    'dbname'=>'test2',
    'uri'=>'mongodb://test:123456@11:11:11:34/admin'
);

$config['db']['test']['master'] = array(
    'host'=>'test.mysql.com',
    'port'=>'3306',
    'user'=>'ROOT',
    'pass'=>'',
    'db'=>'test'
);

$config['db']['test']['slave'] = array(
    'host'=>'test.mysql.com',
    'port'=>'3306',
    'user'=>'ROOT',
    'pass'=>'',
    'db'=>'test'
);
