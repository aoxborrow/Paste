<?php

abstract class Template_Controller {

	public function __construct() {
		
		echo '<html><body style="padding: 50px;">';
		echo '<a href="/">home</a> / '.Router::$uri.'<br/>';
		
	}

	public function __call($method, $args) {
		
		// print_r(func_get_args());		
		echo 'Page not found: '.$method;
		
	}
	
}