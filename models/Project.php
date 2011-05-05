<?php

class Project extends Mustache {
	
	// project name and link id
	public $name;
	
	// define mustache template	
	public $_template = 'project.mustache';
	
	// path to project .yaml files
	public static $yaml_path = 'views/projects/';	
	
	public function __construct($name) {
				
		// set project name
		$this->name = $name;
		
		// load project yaml
		$values = sfYaml::load(APPPATH.self::$yaml_path.$this->name.'.yaml');
		
		// assign to values of this view model
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
		
	}
	
	public static function factory($name = NULL) {

		return new Project($name);

	}
	
	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}