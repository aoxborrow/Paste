<?php

class Template extends Mustache {
	
	// page title
	public $title = '';
	
	// page content
	public $content = '';

	// menu view model
	public $menu;
	
	// used for selected menu item
	public $current_page = NULL;
		
	// mustache template
	protected $_template = 'template.mustache';
	

	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}