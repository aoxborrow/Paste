<?php 

define('APPPATH', __DIR__.'/');
require_once 'libraries/Autoloader.php'; 

// map routes to controllers, longest first, catchall last
Router::execute(array(
	'/work/([a-z]+)/([0-9]+)' => 'Work/show',
	'/work/([a-z]+)' => 'Work/show',
	'/work' => 'Work',
	'/info' => 'Info',
	'/index' => 'Index',
	'/404' => '_404',
));
