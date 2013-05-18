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
	public static $path;

	// request uri
	public static $uri;
	
	// configured routes
	public static $routes = array();

	// init and execute
	public function run() {

		// register autoloader
		// spl_autoload_register('Paste::autoloader');

		// start benchmark
		$execution_start = microtime(TRUE);

		// two useful constants for formatting text
		define('TAB', "\t"); 
		define('EOL', "\n");

		// full path to where index.php resides, with trailing slash
		self::$path = rtrim(getcwd(), '/').'/';

		// no uri supplied, detect it
		if (! self::$uri)
			self::$uri = self::uri();

		// add catch-all default route for Page controller
		self::route('(.*)', 'Paste\Page::get');
		
		// execute routing 
		if ($route = self::routing()) {

			// execute routed callback
			call_user_func_array($route['callback'], $route['parameters']);

		} else {
		
			// something went sideways -- no matched route -- try 404
			Page::get('404');
			
		}
		
		// DEBUG INFOS
		// stop benchmark, get execution time
		$execution_time = number_format(microtime(TRUE) - $execution_start, 4);

		// add benchmark time to end of HTML
		// echo EOL.EOL.'<!-- Execution Time: '.self::$execution_time.', Included Files: '.count(get_included_files()).' -->';
		echo EOL.'<br/>Execution Time: '.$execution_time.', Included Files: '.count(get_included_files()).', Memory Usage: '.number_format(round(memory_get_usage(TRUE)/1024, 2)).'KB';
		

	}

	// simple router, takes uri and maps arguments
	public static function routing() {
		
		// callback and params
		$routed_callback = NULL;
		$routed_parameters = array();
		
		// match URI against routes and execute glorious vision
		foreach (self::$routes as $route => $callback) {
			
			// e.g. 'blog/post/([A-Za-z0-9]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', self::$uri, $params)) {
				
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
	
	// find URI from CLI or PHP_SELF
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

	// simple autoloader
	public static function autoloader($class) {
		
		// remove prefixed slash
		if ($class[0] === "\\")
			$class = substr($class, 1);

		// only try to autoload Paste classes here
		if (strpos($class, 'Paste') !== 0)
			return;
		
		// remove namespace
		$class = explode('\\', $class);
		$class = array_pop($class);
		
		// just load classes from this dir
		if (is_file(__DIR__."/$class.php"))
			require __DIR__."/$class.php";

	}
}