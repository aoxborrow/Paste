<?php

// setup error reporting
error_reporting(E_ALL);
ini_set('display_errors', TRUE);

// check PHP version
version_compare(PHP_VERSION, '5.3', '<') and exit('Paste requires PHP 5.3 or newer.');

// need this one
require 'Paste/Paste.php';

// user defined routing
// 'route regex' => any valid callback
// matched tokens from the regex will be passed as parameters
// e.g. 'blog/post/([A-Za-z0-9]+)' => 'Class::method',
Paste::$routes = array(
	// example user defined blog route
	'blog/post/([A-Za-z0-9-_]+)' => function($slug) { 
		echo "Example callback route, slug: <b>$slug</b><br/>";
	},
);

Paste::run();

