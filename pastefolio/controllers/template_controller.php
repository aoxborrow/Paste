<?php

class template_controller {

	// template view model
	public $template;

	// current section
	public $current_section;

	// current page
	public $current_page;

	// site mustache template
	public $site_template = 'site.mustache';


	public function __construct() {

		// TODO: extend common controller class to provide this and other common methods?
		if (Pastefolio::$instance == NULL) {

			// set router instance to controller
			Pastefolio::$instance = $this;
		}

		// set current section to controller name
		$this->current_section = Pastefolio::$controller;

		// set current section to controller name
		$this->current_page = Pastefolio::$method;

		// setup main template
		$this->template = new Template($this->site_template);


	}

	public function __call($method, $args) {

		// print_r(func_get_args());
		return $this->error_404();

	}

	public function error_404() {

		header('HTTP/1.1 404 File Not Found');

		$this->template->content = '<h1>Page not found!</h1>';
		echo '<h1>Page not found!</h1>';

	}

	public function _render() {

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// render the template after controller execution
		return $this->template->render();

	}

}