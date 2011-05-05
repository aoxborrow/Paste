<?php

abstract class Template_Controller {

	public function __construct() {
		
		echo '<html><body style="padding: 50px;">';
		echo '<div style="width: 80%; height: 30px;"><a href="/">home</a> / '.str_replace('/', ' / ', Router::$uri).'</div>';
		
	}

	public function __call($method, $args) {
		
		// print_r(func_get_args());		
		echo 'Page not found: '.$method;
		
	}
	
}