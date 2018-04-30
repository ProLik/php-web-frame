<?php
$config['interceptor']['default'][] = 'UserAuth';
$config['interceptor']['exception']['UserAuth'] = array(
    'Resource',
    'Login'
);