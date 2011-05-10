<?php

// project view model
class Project extends Mustache {
	
	// project name and link id
	public $name;
	
	// current project page
	public $page = 1;
	
	// number of project pages
	public $num_pages = 1;
	
	// define mustache template	
	protected $_template = 'project.mustache';
	
	// path to project data files
	protected static $_project_path = 'views/projects/';
	
	// base url for projects
	public static $_project_url = '/work/';
		
	// constructor sets name
	public function __construct($name) {
				
		// set project name
		$this->name = $name;

	}
	
	// factory for chaining methods
	public static function factory($name = NULL) {

		$project = new Project($name);
		
		return $project->load();

	}
	
	// load single project data
	public function load() {
		
		// load project data
		$values = Storage::load(self::$_project_path.$this->name);
		
		// assign to values of this view model
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
		
		// set project pages (based on #images for now)
		$this->num_pages = $this->numpics;		
		
		return $this;
		
	}
	
	// generate previous page url
	public function prev_url() {
		
		// if we're at the first page
		if ($this->page <= 1) {
			
			// try to get previous project
			$prev = Menu::relative_project($this->name, -1);

			// if none available use last project (loop around)
			$prev = ($prev === FALSE) ? Menu::last_project() : $prev;
			
			return self::$_project_url.$prev;
			
		} else {
			
			// otherwise return the previous page
			return self::$_project_url.$this->name.'/'.($this->page - 1);
			
		}
	}	
	
	// generate next page url
	public function next_url() {
		
		// if we're at the last page
		if ($this->page >= $this->num_pages) {
						
			// try to get next project
			$next = Menu::relative_project($this->name, 1);

			// if none available use first project (loop around)
			$next = ($next === FALSE) ? Menu::first_project() : $next;
			
			return self::$_project_url.$next;
			
			
		} else {
			
			// otherwise return the next page
			return self::$_project_url.$this->name.'/'.($this->page + 1);
		
		}
	}	
	
	
	// returns array of all projects
	public static function all_projects() {
		
		$projects = array();
		
		foreach (Storage::list_dir(self::$_project_path) as $name) {
			$projects[] = Project::factory($name)->load();
		}
		
		return $projects;
	}
		
		
	public function render() {
		
		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));
		
	}
	
}