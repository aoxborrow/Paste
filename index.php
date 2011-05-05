<?php 

/* Site Design Goals:
- barebones micro MVC pattern
- simple routing
- only load classes i'll be using
- no html in controllers, super thin controllers
- view models act as both data model and mustache model
- mustache for ultra dumb templates
- yaml for simple data, content management
*/


// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// APPPATH constant with trailing slash for convenience
define('APPPATH', __DIR__.'/');

// register autoloader for libraries, controllers, models
require_once 'libraries/Autoloader.php'; 

// map routes to controllers, define longest first
// generally uses kohana routing conventions: http://docs.kohanaphp.com/general/routing
Router::$routes = array(
	'convert' => 'work/convert', // temporary	
	'work/([a-z]+)/([0-9]+)' => 'work/show/$1/$2', // work item with pagination
	'work/([a-z]+)' => 'work/show/$1', // individual work item
	'_404' => '_404', // 404 controller
	'_default' => 'index', // default controller
);

// match uri to route and instantiate controller
$controller = Router::execute($_SERVER['REQUEST_URI']);

// auto render template
$controller->_render();
