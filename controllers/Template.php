<?php

abstract class Template_Controller {
	
	// template view model
	public $template;

	// default to auto-rendering
	public $auto_render = TRUE;
	
	public function __construct() {
		
		require_once(APPPATH.'libraries/mustache/Mustache.php');
		
		$this->template = new Template;
		$this->template->breadcrumb = str_replace('/', ' / ', Router::$uri);
				
	}

	public function __call($method, $args) {
		
		// print_r(func_get_args());		
		echo 'Page not found: '.$method;
		
	}
	
	public function _render() {
		
		if ($this->auto_render == TRUE) {
			// Render the template when the class is destroyed
			echo $this->template->render();
		}
		
	}
	
}