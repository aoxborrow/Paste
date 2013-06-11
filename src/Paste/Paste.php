<?php 
/**
 * Paste - a super simple "CMS" built around static files and folders.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://github.com/paste/Paste
 * @copyright  (c) 2013 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Paste;

// the main drag
class Paste {

	// full path to where index.php resides, with trailing slash
	public static $app_path;
	
	// full path to content
	public static $content_path;

	// content directory relative to app path
	public static $content_dir = 'content';
	
	// content file extension
	public static $content_ext = '.html';
	
	// instance of Mustache engine with string loading for content pages
	public static $content_engine;

	// content "database"
	public static $content_db;

	// full path to mustache templates
	public static $template_path;

	// template directory relative to app path
	public static $template_dir = 'templates';

	// template file extension
	public static $template_ext = '.stache';
	
	// main instance of Mustache engine
	public static $template_engine;

	// full path to cache for mustache
	public static $cache_path;

	// cache directory relative to app path
	public static $cache_dir = 'cache';

	// request uri
	public static $request_uri;
	
	// benchmark start
	public static $execution_start;
	
	// configured routes
	public static $routes = array();

	// init and execute
	public static function run() {

		// start benchmark
		self::$execution_start = microtime(TRUE);

		// full path to where index.php resides, with trailing slash
		self::$app_path = rtrim(getcwd(), '/').'/';
		
		// full path to content files
		self::$content_path = self::$app_path.self::$content_dir.'/';
	
		// full path to mustache templates
		self::$template_path = self::$app_path.self::$template_dir.'/';

		// full path to cache for mustache
		self::$cache_path = self::$app_path.self::$cache_dir;
		
		// setup main mustache engine for templates
		self::$template_engine = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader(self::$template_path, array('extension' => self::$template_ext)),
			'cache' => is_writable(self::$cache_path) ? self::$cache_path : FALSE,
		));
		
		// string loader mustache engine for content pages
		self::$content_engine = new \Mustache_Engine(array(
			'partials_loader' => new \Mustache_Loader_FilesystemLoader(self::$template_path, array('extension' => self::$template_ext)),
			'cache' => is_writable(self::$cache_path) ? self::$cache_path : FALSE,
		));
		
		// detect request URI
		self::$request_uri = self::uri();

		// add catch-all default route for Page controller
		self::route('(.*)', 'Paste\Content::request');
		
		// execute routing 
		if ($route = self::routing()) {

			// execute routed callback
			call_user_func_array($route['callback'], $route['parameters']);

		} else {
		
			// something went sideways -- no matched route -- try 404
			Content::find('404');
			
		}
	}

	// simple router, takes uri and maps arguments
	public static function routing() {
		
		// callback and params
		$routed_callback = NULL;
		$routed_parameters = array();
		
		// match URI against routes and execute glorious vision
		foreach (self::$routes as $route => $callback) {
			
			// e.g. 'blog/post/([A-Za-z0-9-]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', self::$request_uri, $params)) {
				
				// we have a live one
				if (is_callable($callback)) {
					
					// our route callback
					$routed_callback = $callback;
					
					// parameters are each of the regex matches
					$routed_parameters = array_slice($params, 1);
					
					// no need to look further
					break;
				}
			}
		}

		// no route callback matched, return FALSE
		if (empty($routed_callback))
			return FALSE;

		// return matched route / callback
		return array(
			'callback' => $routed_callback,
			'parameters' => $routed_parameters,
		);

	}
	
	// add route(s)
	public static function route($route, $callback = NULL) {
		
		// passed an array of routes
		if (is_array($route) AND $callback === NULL) {
			
			// merge into $routes
			self::$routes = array_merge(self::$routes, $route);
		
		// passed a single route
		} else {
			
			// set route
			self::route(array($route => $callback));
			
		}
	}
	
	// find URI from REQUEST_URI or CLI
	public static function uri() {

		// get requested URI, or from command line argument if running from CLI
		$uri = (PHP_SAPI === 'cli') ? $_SERVER['argv'][1] : getenv('REQUEST_URI');
		
		// remove query string from URI
		if (FALSE !== $query = strpos($uri, '?'))
			list ($uri, $query) = explode('?', $uri, 2);

		// remove front router (index.php) if it exists in URI
		if (FALSE !== $pos = strpos($uri, 'index.php'))
			$uri = substr($uri, $pos + strlen('index.php'));

		// remove leading and trailing slashes
		return trim($uri, '/');

	}

	// for simple redirects
	public static function redirect($uri = '/') {

		header('Location: '.$uri);
		exit;

	}

}