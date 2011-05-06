<?php

// simple controller router 
// assumes use of mod_rewrite to hide index page
// adapted from Kohana 2.3

class Router {

	// array of routes
	public static $routes;
	
	// routed uri
	public static $uri;

	// controller instance
	public static $instance;

	// routed controller
	public static $controller;

	// routed method
	public static $method = 'index';

	// uri arguments
	public static $arguments = array();

	// taken from Kohana 2.3
	public static function & instance() {

		if (self::$instance === NULL) {

			try {

				// start validation of the controller
				$class = new ReflectionClass(ucfirst(self::$controller).'_Controller');

			} catch (ReflectionException $e) {

				// controller does not exist
				return self::_404();

			}

			// create a new controller instance
			self::$instance = $class->newInstance();

			try {
				// load the controller method
				$method = $class->getMethod(self::$method);

				// method exists
				if (self::$method[0] === '_') {

					// do not allow access to hidden methods
					return self::_404();
				}

				if ($method->isProtected() or $method->isPrivate()) {

					// do not attempt to invoke protected methods
					return self::_404();
				}

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

	public static function & _404() {
		
		// simply call _404 route and exit
		return self::execute('_404');
		
	}

	public static function & execute($uri) {

		// use default route if empty uri
		self::$uri = (empty($uri) OR $uri == '/') ? self::$routes['_default'] : trim($uri, '/');

		// match uri against route
		foreach (self::$routes as $route => $callback) {

			if ($route === '_default' || $route === '_404') continue;

			// trim slashes
			$route = trim($route, '/');
			$callback = trim($callback, '/');
			
			if (preg_match('#^'.$route.'$#u', self::$uri)) {

				if (strpos($callback, '$') !== FALSE) {

					// use regex routing
					self::$uri = preg_replace('#^'.$route.'$#u', $callback, self::$uri);

				} else {
					// standard routing
					self::$uri = $callback;
				}

				// valid route has been found
				$matched = TRUE;
				break;
				
			} 
		}
		
		// deciper controller/method
		$segments = explode('/', self::$uri);

		// controller is first segment
		self::$controller = $segments[0];

		// use default method if none specified
		self::$method = (isset($segments[1])) ? $segments[1] : self::$method;

		// remaining arguments
		self::$arguments = array_slice($segments, 2);

		// instantiate controller
		return self::instance();

	}
	
}