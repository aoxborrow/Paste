<?php

/*
Pastefolio is a simple CMS that uses the MVC pattern and plain HTML files instead of a database.
It uses Mustache for templates and works out of the box on any PHP5 server.

*/

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

TODO: consider combining router, conent and common methods into Pastefolio class, so Pastefolio::instance() represents router
TODO: try indxr
TODO: add lab notes with title, date, summary / integrate with tumblr
TODO: rounded & matted image styles

*/

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// define APPPATH constant with trailing slash for convenience
define('APPPATH', __DIR__.'/');

// define directory where content files are stored
define('CONTENTPATH', __DIR__.'/content/');

// define directory where mustache templates are stored
define('TEMPLATEPATH', __DIR__.'/views/templates/');

// register autoloader for libraries, controllers, models
require_once 'libraries/Autoloader.php';

// using Mustache for templating
require_once 'vendor/mustache/Mustache.php';

// load all content data
Content::init();

// map routes to controllers, define longest first
// generally uses kohana routing conventions: http://docs.kohanaphp.com/general/routing
Router::$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog/page', // default blog page
	'notes/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'notes/archive' => 'blog/archive', // blog archive
	'_default' => 'content', // default content controller
);

// match uri to route and instantiate controller
$controller = Router::execute($_SERVER['REQUEST_URI']);

// auto render controller if available
if (method_exists($controller, '_render')) {
	$controller->_render();
}
