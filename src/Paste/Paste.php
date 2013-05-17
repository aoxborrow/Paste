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

	// full path to content directory
	public static $content_path;

	// full path to template directory
	public static $template_path;

	// configured routes
	public $routes = array();

	// request uri
	public $uri;

	// routed callback
	public $routed_callback;

	// routed callback params
	public $routed_parameters = array();
	
	// controller instance
	public static $controller;
		
	// singleton instance
	private static $instance;
	
	// benchmark execution
	public $execution_start;
	public $execution_time;
	
	// instantiate singleton
	public function __construct() {
		
		// singleton access
		self::$instance = $this;

		// two useful constants for formatting text
		define('TAB', "\t"); 
		define('EOL', "\n");

		// start benchmark
		$this->execution_start = microtime(TRUE);
		
		// register autoloader
		// spl_autoload_register('Paste::autoloader');
		
		// location of index.php
		if (! self::$app_path)
			self::$app_path = getcwd();
		
		// force trailing slash
		self::$app_path = rtrim(self::$app_path, '/').'/';

		// directory where content files are stored
		if (! self::$content_path)
			self::$content_path = self::$app_path.'content/';

		// directory where templates are stored
		if (! self::$template_path)
			self::$template_path = self::$app_path.'templates/';

		// add default route for content controller
		$this->route('_default', function() { 
		
			Content::render();
			
			echo "<br/>Called _default route, URI: ".Paste::instance()->uri."<br/>";
			
		});

	}

	// simple router, takes uri and maps arguments
	public function run($uri = NULL) {
		
		// no uri supplied, detect it
		if ($uri === NULL)
			$uri = self::uri();

		// store requested URI, remove leading and trailing slashes
		$this->uri = trim($uri, '/');
		
		// match URI against routes and execute glorious vision
		foreach ($this->routes as $route => $callback) {

			// e.g. 'blog/post/([A-Za-z0-9]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', $this->uri, $params)) {
				
				// we have a live one
				if (is_callable($callback)) {
					
					// our route callback
					$this->routed_callback = $callback;
					
					// parameters are each of the regex matches
					$this->routed_parameters = array_slice($params, 1);
					
					// no need to look further
					break;
				}
			}
		}

		// no route callback matched, use default route
		if (! $this->routed_callback)
			$this->routed_callback = $this->routes['_default'];
		
		// execute routed callback
		call_user_func_array($this->routed_callback, $this->routed_parameters);
		
		// stop benchmark, get execution time
		$this->execution_time = number_format(microtime(TRUE) - $this->execution_start, 4);

		// add benchmark time to end of HTML
		// echo EOL.EOL.'<!-- Execution Time: '.self::$execution_time.', Included Files: '.count(get_included_files()).' -->';
		$_mem_usage = memory_get_usage(TRUE);
		echo EOL.'<br/>Execution Time: '.$this->execution_time.', Included Files: '.count(get_included_files()).', Memory Usage: '.number_format(round(memory_get_usage(TRUE)/1024, 2)).'KB';

	}
	
	// add route(s)
	public function route($route, $callback = NULL) {
		
		// passed an array of routes
		if (is_array($route) AND $callback === NULL) {
			
			// merge into $routes
			$this->routes = array_merge($this->routes, $route);
		
		// passed a single route
		} else {
			
			// set route
			$this->routes[$route] = $callback;
			
		}
		
		// return instance for method chaining
		return $this;
	}
	
	// singleton instance
	public static function instance() {

		// use existing instance
		if (! isset(self::$instance)) {

			// create a new instance
			self::$instance = new Paste;
		}

		// return instance
		return self::$instance;

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