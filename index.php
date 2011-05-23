<?php
// Pastefolio bootstrap

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// define directory where Pastefolio is located
define('APPPATH', __DIR__.'/');

// define directory where content files are stored
define('CONTENTPATH', __DIR__.'/content/');

// define directory where mustache templates are stored
define('TEMPLATEPATH', __DIR__.'/views/templates/');

// using Mustache for templating: https://github.com/bobthecow/mustache.php
require_once APPPATH.'vendor/mustache/Mustache.php';

// core Pastefolio class
require_once APPPATH.'libraries/Pastefolio.php';

// register class autoloader
spl_autoload_register(array('Pastefolio', 'autoloader'));

// traverse content directory and load all content
// TODO: pass in content path here
Pastefolio::$pages = Page::load_path('/');

// map routes to controllers, define longest first
// generally uses kohana routing conventions: http://docs.kohanaphp.com/general/routing
Pastefolio::$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog', // default blog page
	'notes/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'notes/archive' => 'blog/archive', // blog archive
	'_default' => 'content', // default content controller
);

// match uri to route and instantiate controller
$controller = Pastefolio::request($_SERVER['REQUEST_URI']);

// auto render controller if available
if (method_exists($controller, '_render')) {

	echo $controller->_render();

}
