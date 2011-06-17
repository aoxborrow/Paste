<?php
// Pastefolio bootstrap.php

// system path defaults, should be configured in index.php
// directory where content files are stored
if (! isset($content_path))
	$content_path = 'content';

// directory where mustache templates are stored
if (! isset($template_path))
	$template_path = 'templates';

// directory for cache (must be writeable)
if (! isset($cache_path))
	$cache_path = 'templates';

// directory where Pastefolio is located
if (! isset($app_path))
	$app_path = dirname(__FILE__);

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
if (! isset($routes))
	$routes = array('_default' => 'content'); // default content controller

// setup error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);

// check PHP version
version_compare(PHP_VERSION, '5.2', '<') and exit('Pastefolio requires PHP 5.2 or newer.');

// global paths with trailing slash for convenience
define('APPPATH', str_replace('\\', '/', realpath($app_path)).'/');
define('CONTENTPATH', str_replace('\\', '/', realpath(DOCROOT.$content_path)).'/');
define('TEMPLATEPATH', str_replace('\\', '/', realpath(DOCROOT.$template_path)).'/');
define('CACHEPATH', str_replace('\\', '/', realpath(DOCROOT.$cache_path)).'/');

// start benchmark
$benchmark_start = microtime(TRUE);

// core Pastefolio class
require_once APPPATH.'libraries/Pastefolio.php';

// using Mustache for templating: https://github.com/bobthecow/mustache.php
require_once APPPATH.'libraries/Mustache/Mustache.php';

// register class autoloader
spl_autoload_register(array('Pastefolio', 'autoloader'));

// setup cache directory
Cache::$directory = CACHEPATH;

// set cache lifetime in seconds. 0 or FALSE disables cache
Cache::$lifetime = $cache_time;

// assign user configured routes
Pastefolio::$routes = $routes;

// match requested uri to route and instantiate controller
Pastefolio::request($_SERVER['REQUEST_URI']);

// check if cached content is available
if (empty(Pastefolio::$instance->output)) {

	// execute controller method
	Pastefolio::execute();

	// auto render controller template if available
	if (method_exists(Pastefolio::$instance, '_render'))
		Pastefolio::$instance->_render();

}

// echo buffered output
echo Pastefolio::$instance->output;


// stop benchmark, get execution time
$benchmark_time = number_format(microtime(TRUE) - $benchmark_start, 4);

// add benchmark time to end of HTML
echo '<!-- Execution Time: '.$benchmark_time.', Included Files: '.count(get_included_files()).' -->';

// clean up config vars
unset($app_path, $content_path, $template_path, $routes, $cache, $benchmark_start, $benchmark_time);




