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
- abstract a separate "pastefolio" core system into submodule on github, create demo app with basic template

TODO: create sections and pages from content folder structure, catch-all controller
TODO: simplify HTML project loading, load image data, full tags, clear existing, verify current page is viable
TODO: add lab notes with title, date, summary / integrate with tumblr
TODO: rounded & matted image styles

*/

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// define APPPATH constant with trailing slash for convenience
define('APPPATH', __DIR__.'/');

// register autoloader for libraries, controllers, models
require_once 'libraries/Autoloader.php';

// using Mustache for templating
require_once 'libraries/mustache/Mustache.php';

// using YAML for data storage
// require_once 'libraries/yaml/lib/sfYaml.php';

// map routes to controllers, define longest first
// generally uses kohana routing conventions: http://docs.kohanaphp.com/general/routing

// automatically add routes for sections
foreach (Storage::list_sections() as $section) {
	Router::$routes[$section.'/([A-Za-z0-9]+)'] = 'sections/'.$section.'/$1';
}

Router::$routes += array(
	// 'projects/([A-Za-z0-9]+)' => 'projects/$1', // view projects
	'_404' => 'template/error_404', // define 404 method
	'_default' => 'index', // default controller
);

//die(print_r(Router::$routes));

// match uri to route and instantiate controller
$controller = Router::execute($_SERVER['REQUEST_URI']);

// auto render template of controller
$controller->_render();
