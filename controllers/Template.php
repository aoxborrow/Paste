<?php

abstract class Template_Controller {

	public function __construct() {
		
		echo '<html><body style="padding: 50px;">';
		echo '<a href="/">home</a><br/>';
		
	}

	public function __call($method, $args) {
		
		echo 'Page not found: '.$method;
		
	}
	
}