<?php
/**
 * Paste - a super simple "CMS" built around static files and folders.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://github.com/paste/Paste
 * @copyright  (c) 2013 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

class Paste {

	// configured routes
	public static $routes = array();

	// request uri
	public static $uri;

	// routed callback
	public static $routed_callback;

	// routed callback params
	public static $routed_parameters = array();

	// simple router, takes uri and maps arguments to 
	public static function run($uri = FALSE) {
		
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
		if (file_exists(APP_PATH.'libraries/'.$class.'.php')) {

			require_once APP_PATH.'libraries/'.$class.'.php';
			return TRUE;

		} 

		// couldn't find the class
		return FALSE;
	}
}