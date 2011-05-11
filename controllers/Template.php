<?php

class template_controller {

	// template view model
	public $template;

	public function __construct() {

		// setup mustache template
		$this->template = new Mustache(file_get_contents(APPPATH.'views/templates/site.mustache'));

		// default title
		$this->template->title = 'pastetwo';

		// content area
		$this->template->content = '';

		// set current section to controller name
		$this->template->current_section = Router::$controller;

		// set current section to controller name
		$this->template->current_page = Router::$method;

		// define project menu
		Menu::$menu = array(
			'about' => array('About'),
			'notes' => array('Lab Notes', array(
			)),			
			'projects' => array('Projects', array(
				'sorenson' => 'Sorenson',
				'globallr' => 'Global Leasing',
				'ifloorplan' => 'iFloorPlan',
				'twl' => 'T.W. Lewis',
				'killington' => 'Killington',
				'kb' => 'KB Home',
				'mvr' => 'Monte Vista',
				'contact' => 'Contact Design',
				'silverplatter' => 'Silver Platter',
				'tropical' => 'Tropical Salvage',
				//'rockwell' => 'Rockwell Partners',
				//'viridian' => 'Viridian Group',
				//'trade' => 'Design Trade',
				'modified' => 'Modified Arts',
				'blufish' => 'Blufish Design',
				'logos' => 'Logos',
			)),
			
		);
		

		// init menu view model
		$this->template->menu = new Menu;		

		// bind current_section in menu view to template var
		$this->template->menu->current_section =& $this->template->current_section;

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