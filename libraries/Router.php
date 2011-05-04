<?php

class Router {
	
	// assumes use of mod_rewrite to hide index page
	protected static $index = 'index.php';

	// default controller
	protected static $default_controller = 'index';

	// default method
	protected static $default_method = 'index';
	

	public static function execute($routes) {
				
		$uri = ($_SERVER['REQUEST_URI'] == '/') ? '/'.self::$default_controller : $_SERVER['REQUEST_URI'];
		$params = '';		
					
		foreach ($routes as $route => $callback) {
						
			if (eregi($route, $uri, $matches) !== FALSE) {

				// remove the original match from arguments
				$params = array_slice($matches, 1);
				break;

			}
		}

		// if nothing matched this will execute the last route defined as the catchall
	 	//  {
		// using a capital letter denotes controller
		if ($callback[0] < 'a') {

			// add default method if not specified
			if (strpos($callback, '/') === FALSE) 
				$callback .= '/'.self::$default_method;

			// deciper controller/method
			list($controller, $method) = explode('/', $callback);
			
			// enforce _Controller suffix
			$controller = $controller.'_Controller';

			// if (class_exists($controller.'_Controller', TRUE) AND method_exists($controller.'_Controller', $method)) {
			if (class_exists($controller, TRUE) AND is_callable($controller, $method)) {

				// instantiate controller
				$controller = new $controller;

				// call valid method with URI as $params
				$controller->$method($params);
			} 
			
		// allow regular function callbacks
		} elseif (is_callable($callback)) {
			
			call_user_func($callback, $params);
			
		} 

}

}