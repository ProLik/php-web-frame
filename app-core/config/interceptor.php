<?php
$config['interceptor']['default'][] = 'Guid';

$config['interceptor']['default'][] = 'Version';

$config['interceptor']['default'][] = 'UserAuth';
$config['interceptor']['exception']['UserAuth'] = array(
    'Resource',
    'Login',
    'Browser'
);