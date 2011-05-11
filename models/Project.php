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
	public static $_project_url = '/projects/';
	
	// define mustache template	
	protected $_template = 'templates/project.mustache';
		
	public $extension = 'jpg';
	
	public $content = '';
	
	public $images = array();
	
	// factory for chaining methods
	public static function factory($name, $current_image = 1) {

		// instantiate project
		$project = new Project;
		
		// set project name
		$project->name = $name;
		
		// set current image to display
		$project->current_image = $current_image;
		
		return $project->load();

	}
	
	// load single project data
	public function load() {
		
		if (FALSE === $html = @file_get_contents(APPPATH.'views/projects/'.$this->name.'.html')) {
			return FALSE;
		}
		
		$dom = DOMDocument::loadHTML($html);
		
		// count number of image tags
		$imgs = $dom->getElementsByTagName('img');
		
		// TODO: should just delete the other image tags and leave the current one based on current_image
		
		$i = 1;
		foreach ($imgs as $img) {
			$this->images[$i] = array(
				'src' => $img->getAttribute('src'),
				'alt' => $img->getAttribute('alt'),
				'height' => $img->getAttribute('height'),
				'width' => $img->getAttribute('width'),
			);
			$i++;
		}

		$this->extension = pathinfo($this->images[$this->current_image]['src'], PATHINFO_EXTENSION);		
		$this->caption = $this->images[$this->current_image]['alt'];		
		
		$this->content = self::strip_only_tags($html, 'img');
		
		$this->num_images = count($this->images);
		
		return $this;
		
	}
	
	public static function strip_only_tags($str, $tags, $stripContent=false) {
		$content = '';
		if(!is_array($tags)) {
			$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
			if(end($tags) == '') array_pop($tags);
		}
		foreach($tags as $tag) {
			if ($stripContent)
				 $content = '(.+</'.$tag.'(>|\s[^>]*>)|)';
			 $str = preg_replace('#</?'.$tag.'(>|\s[^>]*>)'.$content.'#is', '', $str);
		}
		return $str;
	}	
			
	
	// load single project data
	public function old_load() {
		
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