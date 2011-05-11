<?php

// simple controller router
// assumes use of mod_rewrite to hide index page
// adapted from Kohana

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

	// adapted from Kohana
	public static function & instance() {

		if (self::$instance === NULL) {

			try {

				// start validation of the controller
				$class = new ReflectionClass(self::$controller.'_controller');

			} catch (ReflectionException $e) {

				// controller does not exist
				return self::execute('_404');

			}

			// create a new controller instance
			self::$instance = $class->newInstance();

			try {
				// load the controller method
				$method = $class->getMethod(self::$method);

				// method exists
				if (self::$method[0] === '_') {

					// do not allow access to hidden methods, unless 404
					return self::execute('_404');
				}

				if ($method->isProtected() or $method->isPrivate()) {

					// do not attempt to invoke protected methods
					return self::execute('_404');
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

	// take uri and map controller, method and arguments
	public static function & execute($uri) {

		// use default route if empty uri
		self::$uri = (empty($uri) OR $uri == '/') ? self::$routes['_default'] : trim($uri, '/');

		// match uri against route
		foreach (self::$routes as $route => $callback) {

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
	
	// for simple redirect
	public static function redirect($url = '/') {

		header('Location: '.$url);
		exit;

	}	

}