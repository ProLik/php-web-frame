<?php
$config['router']['Resource'] = array(
	'^\/([a-z]+)\/resource\/([a-z]+)\/(.+)\.(css|js)$',
);

$config['router']['Cache'] = array(
	'^\/cache$'
);

$config['router']['Browser'] = array(
	'^\/browser$'
);