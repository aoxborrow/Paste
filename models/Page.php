<?php

// page view model
class Page extends Mustache {

	// path to page content, relative to CONTENTPATH
	public $path;

	// mustache template, relative to TEMPLATEPATH
	public $template = 'project.mustache'; // allow setting this in section variable

	// page name and link id
	public $name;

	// page title, used in menu
	public $title;

	// page description, optional
	public $description;

	// page content
	public $content;

	// redirect URL for creating aliases
	public $redirect;

	// thumbnail for gallery display
	public $thumb;

	// parent section
	public $section;

	// page is a section index
	public $is_section = FALSE;

	// visible in menu
	public $is_visible = TRUE;


	// constructor sets name and loads data
	public function __construct($name, $path) {

		// set project name
		$this->name = $name;
		$this->path = trim($path, '/');

		// load content data
		$this->load();

	}

	// factory for chaining methods
	public static function factory($name) {

		// find page in Content database

		foreach (Content::$pages as $page) {
			if ($page->name == $name)
				return $page;
		}

		/*
		// instantiate page model
		$page = new Page;
		$page->name = $name;

		// load page content if path is defined
		return ($path === NULL) ? $page : $page->load();
		*/

	}

	// load individual content page
	public function load() {

		if (FALSE === ($html = @file_get_contents(realpath(CONTENTPATH.$this->path)))) {
			return FALSE;
		}

		$dom = DOMDocument::loadHTML($html);

		$this->title = @$dom->getElementsByTagName('h1')->item(0)->nodeValue;
		$this->content = $html;

	}

/*
	// returns array of all projects
	public static function all_projects() {

		$projects = array();

		foreach (Content::list_dir(self::$_project_path) as $name) {
			$projects[] = Project::factory($name)->load();
		}

		return $projects;
	}
	*/


	public function render() {

		return parent::render(file_get_contents(realpath(TEMPLATEPATH.$this->template)));

	}

}