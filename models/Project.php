<?php

// project view model
class Project extends Mustache {
	
	// project name and link id
	public $name;
	
	// define mustache template	
	protected $_template = 'project.mustache';
	
	// path to project data files
	protected static $_project_path = 'views/projects/';	
	
	// constructor sets name
	public function __construct($name) {
				
		// set project name
		$this->name = $name;
						
	}
	
	// factory for chaining methods
	public static function factory($name = NULL) {

		return new Project($name);

	}	
	
	// load single project data
	public function load() {
		
		// load project data
		$values = Content::load(self::$_project_path.$this->name);
		
		// assign to values of this view model
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
		
		return $this;
		
	}
	
	// returns array of all projects
	public static function all_projects() {
		
		$projects = array();
		
		foreach (Content::list_dir(self::$_project_path) as $name) {
			$projects[] = Project::factory($name)->load();
		}
		
		return $projects;
	}
		
		
	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}