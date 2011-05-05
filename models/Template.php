<?php

class Template extends Mustache {
	
	// default page title
	public $title = 'pastetwo';
	
	// page content
	public $content = '';
	
	// define mustache template
	public $_template = 'template.mustache';
	
	public function __construct() {
		
		// setup template defaults n' such

	}
		
	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}