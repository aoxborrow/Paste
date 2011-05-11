<?php 

/* Site Design Goals:
- barebones micro MVC pattern
- simple routing
- only load classes i'll be using
- no html in controllers, super thin controllers
- view models act as both data model and mustache model
- mustache for ultra dumb templates
- yaml for simple data, content management
- experiment with various new techs
- use history API for loading project content: http://html5demos.com/history/

TODO: move menu array somewhere
TODO: add ability for optional image captions
TODO: allow images to be of different extensions
TODO: add Jquery elements from boilerplate
TODO: add #hash routing for projects and pages
TODO: add lab notes with title, date, summary?


*/

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// APPPATH constant with trailing slash for convenience
define('APPPATH', __DIR__.'/');

// register autoloader for libraries, controllers, models
require_once 'libraries/Autoloader.php'; 

// using Mustache for templating
require_once 'libraries/mustache/Mustache.php';

// using YAML for data storage
require_once 'libraries/yaml/lib/sfYaml.php';

// map routes to controllers, define longest first
// generally uses kohana routing conventions: http://docs.kohanaphp.com/general/routing
Router::$routes = array(
	'projects/([A-Za-z0-9]+)' => 'projects/$1', // view project
	'projects/([A-Za-z0-9]+)/([0-9]+)' => 'projects/$1/$2', // project with pagination	
	'_404' => 'template/error_404', // define 404 method
	'_default' => 'index', // default controller
);

// match uri to route and instantiate controller
$controller = Router::execute($_SERVER['REQUEST_URI']);

// auto render template of controller
$controller->_render();
