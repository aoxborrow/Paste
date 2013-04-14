<?php
/**
 * Pastefolio - a simple portfolio CMS. This class provides autoloader, routing and other common methods.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://pastefolio.com
 * @copyright  (c) 2011 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

class Pastefolio {

	// configured routes
	public static $routes = array();

	// request uri
	public static $current_uri;

	// routed uri
	public static $routed_uri;

	// controller instance
	public static $instance;

	// routed controller
	public static $controller;

	// routed method, index is default
	public static $method = 'index';

	// uri arguments
	public static $arguments = array();


	// instantiate controller
	public static function & instance() {

		if (self::$instance === NULL) {

			try {
				
				// start validation of the controller
				$class = new ReflectionClass(self::$controller.'_controller');

			} catch (ReflectionException $e) {

				// controller does not exist
				return self::execute('_default');

			}

			// create a new controller instance
			self::$instance = $class->newInstance();

			try {

				// load the controller method
				$method = $class->getMethod(self::$method);

				// do not allow access to hidden methods
				if (self::$method[0] === '_')
					return self::execute('_default');

				// do not attempt to invoke protected methods
				if ($method->isProtected() or $method->isPrivate())
					return self::execute('_default');

				// default arguments
				$arguments = self::$arguments;

			} catch (ReflectionException $e) {

				// use __call instead
				$method = $class->getMethod('__call');

				// use arguments in __call format
				$arguments = array(self::$method, self::$arguments);
			}

			// execute the controller method
			$method->invokeArgs(self::$instance, $arguments);

		}

		return self::$instance;
	}


	// simple router, takes uri and maps controller, method and arguments
	public static function execute($uri) {

		// remove query string from URI
		if (($query = strpos($uri, '?')) !== FALSE)
			list ($uri, $query) = explode('?', $uri, 2);

		// store requested URI on first run only
		if (self::$current_uri === NULL)
			self::$current_uri = trim($uri, '/');

		// matches a defined route
		$matched = FALSE;

		// match URI against route
		foreach (self::$routes as $route => $callback) {

			// trim slashes
			$route = trim($route, '/');
			$callback = trim($callback, '/');

			if (preg_match('#^'.$route.'$#u', self::$current_uri)) {

				if (strpos($callback, '$') !== FALSE) {

					// use regex routing
					self::$routed_uri = preg_replace('#^'.$route.'$#u', $callback, self::$current_uri);

				} else {

					// standard routing
					self::$routed_uri = $callback;

				}

				// valid route has been found
				$matched = TRUE;
				break;

			}
		}

		// no route matches found, use default route
		if (! $matched)
			self::$routed_uri = self::$routes['_default'];

		// decipher controller/method
		$segments = explode('/', self::$routed_uri);

		// controller is first segment
		self::$controller = $segments[0];

		// use default method if none specified
		self::$method = (isset($segments[1])) ? $segments[1] : self::$method;

		// remaining arguments
		self::$arguments = array_slice($segments, 2);
		
		// instatiate controller
		self::instance();

	}

	// for simple redirects
	public static function redirect($url = '/') {

		header('Location: '.$url);
		exit;

	}

	public static function autoloader($class) {

		// return if class already exists
		if (class_exists($class, FALSE))
			return TRUE;

		// controller
		if (FALSE !== $pos = strpos($class, '_controller')) { 
			
			// strip _controller suffix
			$class = substr($class, 0, $pos);
			
			// try the controllers folder
			if (file_exists(APP_PATH.'controllers/'.$class.'.php')) {

				require_once(APP_PATH.'controllers/'.$class.'.php');
				return TRUE;
				
			}

		// try the libraries folder
		} elseif (file_exists(APP_PATH.'libraries/'.$class.'.php')) {

			require_once(APP_PATH.'libraries/'.$class.'.php');
			return TRUE;

		} 

		// couldn't find the file
		return FALSE;
	}
}