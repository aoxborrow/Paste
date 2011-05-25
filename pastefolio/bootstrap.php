<?php
// Pastefolio bootstrap.php

// system path defaults, should be configured in index.php
// directory where Pastefolio is located
if (! isset($app_path))
	$app_path = 'pastefolio';

// directory where content files are stored
if (! isset($content_path))
	$content_path = 'content';

// directory where mustache templates are stored
if (! isset($template_path))
	$template_path = 'templates';

// directory for cache (must be writeable)
if (! isset($cache_path))
	$cache_path = 'templates';

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
if (! isset($routes))
	$routes = array('_default' => 'content'); // default content controller

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// check PHP version
version_compare(PHP_VERSION, '5.2', '<') and exit('Pastefolio requires PHP 5.2 or newer.');

// get indx.php pathinfo
$pathinfo = pathinfo(__FILE__);

// location of index.php
define('DOCROOT', $pathinfo['dirname'].DIRECTORY_SEPARATOR);

// if defined folders are relative paths, make them absolute
$app_path = file_exists($app_path) ? $app_path : DOCROOT.$app_path;
$content_path = file_exists($content_path) ? $content_path : DOCROOT.$content_path;
$template_path = file_exists($template_path) ? $template_path : DOCROOT.$template_path;
$cache_path = file_exists($cache_path) ? $cache_path : DOCROOT.$cache_path;

// global paths with trailing slash for convenience
define('APPPATH', str_replace('\\', '/', realpath($app_path)).'/');
define('CONTENTPATH', str_replace('\\', '/', realpath($content_path)).'/');
define('TEMPLATEPATH', str_replace('\\', '/', realpath($template_path)).'/');
define('CACHEPATH', str_replace('\\', '/', realpath($cache_path)).'/');


// using Mustache for templating: https://github.com/bobthecow/mustache.php
require_once APPPATH.'libraries/Mustache/Mustache.php';

// core Pastefolio class
require_once APPPATH.'libraries/Pastefolio.php';

// register class autoloader
spl_autoload_register(array('Pastefolio', 'autoloader'));

// traverse content directory and load all content
Pastefolio::$pages = Page::load_path(CONTENTPATH);

// assign user configured routes
Pastefolio::$routes = $routes;

// clean up vars
unset($app_path, $content_path, $template_path, $routes);

// match uri to route and instantiate controller
$controller = Pastefolio::request($_SERVER['REQUEST_URI']);

// auto render controller if available
if (method_exists($controller, '_render')) {

	echo $controller->_render();

}


