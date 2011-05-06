<?php

class Template_Controller {
	
	// template view model
	public $template;
	
	public function __construct() {
		
		// template view model
		$this->template = new Template;
		
		// default title
		$this->template->title = 'pastetwo';

		// set breadcrumb
		// $this->template->breadcrumb = str_replace('/', ' / ', Router::$uri);
		// <div class="breadcrumb"><a href="/">home</a> / {{breadcrumb}}</div>		
		
		// menu view model
		$this->template->menu = new Menu;
		
	
		// bind current_page in menu view to template var
		$this->template->menu->current_page =& $this->template->current_page;

	}

	public function __call($method, $args) {
		
		// print_r(func_get_args());		
		$this->template->content = 'Page not found: '.$method;
		
	}
	
	public function _render() {
		
		if ($this->template->current_page === NULL) {
			// set current page to controller if none set
			$this->template->current_page = Router::$controller;
		}
		
		// Render the template when the class is destroyed
		echo $this->template->render();
		
	}
	
	// for simple redirect
	public static function redirect($url = '/') {

		header('Location: '.$url);

	}
	
}