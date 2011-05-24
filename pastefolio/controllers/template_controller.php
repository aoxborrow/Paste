<?php

class template_controller {

	// current section
	public $current_section;

	// current page
	public $current_page;

	// template view model
	public $template;

	// site mustache template file
	public static $site_template = 'site.mustache';

	// menu mustache template file
	public static $menu_template = 'menu.mustache';


	public function __construct() {

		// TODO: extend common controller class to provide this and other common methods?
		if (Pastefolio::$instance == NULL) {

			// set router instance to controller
			Pastefolio::$instance = $this;
		}

		// get site template
		$site_template = file_get_contents(realpath(TEMPLATEPATH.self::$site_template));

		// setup mustache view
		$this->template = new Mustache($site_template);

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

		// get menu template
		$menu_template = file_get_contents(realpath(TEMPLATEPATH.self::$menu_template));

		// menu view model
		$menu = new Menu;

		// assign menu view to template
		$this->template->menu = new Mustache($menu_template, $menu);

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