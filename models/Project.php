<?php

// project view model
class Project extends Mustache {
	
	// project name and link id
	public $name;
	
	// current project image
	public $current_image = 1;
	
	// number of project pages
	public $num_images = 1;
		
	// base url for projects
	public static $_project_url = '/work/';
	
	// define mustache template	
	protected $_template = 'project.mustache';
			
	// constructor sets name
	public function __construct($name, $current_image) {
				
		// set project name
		$this->name = $name;
		
		// set current image to display
		$this->current_image = $current_image;

	}
	
	// factory for chaining methods
	public static function factory($name = NULL, $current_image = 1) {

		$project = new Project($name, $current_image);
		
		return $project->load();

	}
	
	// load single project data
	public function load() {
		
		// load project data
		$values = Storage::load($this->name);
		
		// assign to values of this view model
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
		
		// set project pages (based on #images for now)
		$this->num_images = $this->numpics;		
		
		return $this;
		
	}
	
	// generate previous page url
	public function prev_url() {
		
		// if we're at the first page
		if ($this->current_image <= 1) {
			
			// try to get previous project
			$prev = Menu::relative_project($this->name, -1);

			// if none available use last project (loop around)
			$prev = ($prev === FALSE) ? Menu::last_project() : $prev;
			
			return self::$_project_url.$prev;
			
		} else {
			
			// otherwise return the previous page
			return self::$_project_url.$this->name.'/'.($this->current_image - 1);
			
		}
	}	
	
	// generate next page url
	public function next_url() {
		
		// if we're at the last page
		if ($this->current_image >= $this->num_images) {
						
			// try to get next project
			$next = Menu::relative_project($this->name, 1);

			// if none available use first project (loop around)
			$next = ($next === FALSE) ? Menu::first_project() : $next;
			
			return self::$_project_url.$next;
			
			
		} else {
			
			// otherwise return the next page
			return self::$_project_url.$this->name.'/'.($this->current_image + 1);
		
		}
	}	
		
	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}