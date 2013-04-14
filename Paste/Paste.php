<?php
/**
 * Paste - a super simple "CMS" built around static files and folders.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://github.com/paste/Paste
 * @copyright  (c) 2013 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

// two useful constants for formatting text
define('TAB', "\t"); 
define('EOL', "\n");

// the main drag
class Paste {

	// full path to where index.php resides
	public static $APP_PATH;

	// full path to content directory
	public static $CONTENT_PATH;

	// full path to template directory
	public static $TEMPLATE_PATH;

	// full path to cache directory (must be writeable)
	public static $CACHE_PATH;

	// configured routes
	public static $routes = array();

	// request uri
	public static $uri;

	// routed callback
	public static $routed_callback;

	// routed callback params
	public static $routed_parameters = array();

	// benchmark execution
	public static $execution_start;
	public static $execution_time;

	// init Paste, setup paths and execute router
	public static function run() {
		
		// start benchmark
		self::$execution_start = microtime(TRUE);
		
		// register autoloader
		spl_autoload_register(array('Paste', 'autoloader'));
		
		// location of index.php
		if (! self::$APP_PATH)
			self::$APP_PATH = getcwd();
		
		// ensure trailing slash
		self::$APP_PATH = rtrim(self::$APP_PATH, '/').'/';

		// directory where content files are stored
		if (! self::$CONTENT_PATH)
			self::$CONTENT_PATH = realpath(self::$APP_PATH.'content');

		// directory where templates are stored
		if (! self::$TEMPLATE_PATH)
			self::$TEMPLATE_PATH = realpath(self::$APP_PATH.'templates');

		// directory for cache (must be writeable)
		if (! self::$CACHE_PATH)
			self::$CACHE_PATH = realpath(self::$APP_PATH.'cache');

		// setup cache directory
		Cache::$directory = self::$CACHE_PATH;

		// cache lifetime in seconds. 0 or FALSE disables cache
		Cache::$lifetime = 0;
		
		// merge user defined routes over defaults
		self::$routes = array_merge(array(
	
			// default content controller
			'_default' => function() { 
		
				echo "Called _default route, URI: ".Paste::$uri."<br/>";
				
			}), self::$routes);

		// match routes and execute glorious vision
		self::router();

		// stop benchmark, get execution time
		self::$execution_time = number_format(microtime(TRUE) - self::$execution_start, 4);

		// add benchmark time to end of HTML
		// echo EOL.EOL.'<!-- Execution Time: '.self::$execution_time.', Included Files: '.count(get_included_files()).' -->';
		echo EOL.'<br/>Execution Time: '.self::$execution_time.', Included Files: '.count(get_included_files());

	}

	// simple router, takes uri and maps arguments to 
	public static function router($uri = FALSE) {
		
		// no uri supplied, detect it
		if ($uri === FALSE)
			$uri = self::uri();

		// store requested URI, remove leading and trailing slashes
		self::$uri = trim($uri, '/');

		// match URI against route
		foreach (self::$routes as $route => $callback) {

			// e.g. 'blog/post/([A-Za-z0-9]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', self::$uri, $params)) {
				
				// we have a live one
				if (is_callable($callback)) {
					
					// our route callback
					self::$routed_callback = $callback;
					
					// parameters are each of the regex matches
					self::$routed_parameters = array_slice($params, 1);
					
					// no need to look further
					break;
				}
			}
		}
		

		// no route callback matched, use default route
		if (! self::$routed_callback)
			self::$routed_callback = self::$routes['_default'];
		
		// execute routed callback
		call_user_func_array(self::$routed_callback, self::$routed_parameters);

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

		// remove leading and trailing slashes if not root
		return trim($uri, '/');

	}

	// for simple redirects
	public static function redirect($uri = '/') {

		header('Location: '.$uri);
		exit;

	}

	// simple autoloader, could easily add a vendor dir
	public static function autoloader($class) {

		// return if class already exists
		if (class_exists($class, FALSE))
			return TRUE;

		// well, try the libraries folder already
		if (file_exists(__DIR__."/$class.php")) {

			require_once __DIR__."/$class.php";
			return TRUE;

		} 

		// couldn't find the class
		return FALSE;
	}
}