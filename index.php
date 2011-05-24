<?php
// Pastefolio index.php

// define system paths, can be absolute or relative to this file
// directory where Pastefolio is located
$app_path = '';

// directory where content files are stored
$content_path = 'content';

// directory where mustache templates are stored
$template_path = 'views/templates';

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog', // default blog page
	'notes/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'notes/archive' => 'blog/archive', // blog archive
	'_default' => 'content', // default content controller
);

// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------

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

// global paths with trailing slash for convenience
define('APPPATH', str_replace('\\', '/', realpath($app_path)).'/');
define('CONTENTPATH', str_replace('\\', '/', realpath($content_path)).'/');
define('TEMPLATEPATH', str_replace('\\', '/', realpath($template_path)).'/');

// using Mustache for templating: https://github.com/bobthecow/mustache.php
require_once APPPATH.'vendor/mustache/Mustache.php';

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


