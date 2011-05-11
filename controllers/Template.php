<?php

class Template_Controller {

	// template view model
	public $template;

	public function __construct() {

		// setup mustache template
		$this->template = new Mustache(file_get_contents(APPPATH.'views/templates/site.mustache'));

		// default title
		$this->template->title = 'pastetwo';

		// content area
		$this->template->content = '';

		// set current page to controller name
		$this->template->current_page = Router::$controller;

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
		return $this->error_404();

	}

	public function error_404() {

		header('HTTP/1.1 404 File Not Found');

		$this->template->content = '<h1>Page not found!</h1>';

	}

	public function _render() {

		// render the template after controller execution
		echo $this->template->render();

	}

}