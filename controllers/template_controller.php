<?php

class template_controller {

	// current section
	public $current_section;

	// current page
	public $current_page;

	// template view model
	public $template;

	// template mustache file
	public static $_template = 'site.mustache';


	public function __construct() {

		// TODO: extend common controller class to provide this and other common methods?
		if (Pastefolio::$instance == NULL) {

			// set router instance to controller
			Pastefolio::$instance = $this;
		}

		// setup mustache template
		$this->template = new Mustache(file_get_contents(realpath(TEMPLATEPATH.self::$_template)));

		// default title
		$this->template->title = 'Paste Labs';

		// content area
		$this->template->content = '';

		// set current section to controller name
		$this->current_section = Pastefolio::$controller;
		$this->template->current_section =& $this->current_section;

		// set current section to controller name
		$this->current_page = Pastefolio::$method;
		$this->template->current_page =& $this->current_page;

		// init menu view model
		$this->template->menu = new Menu;

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
		return $this->template->render();

	}

}