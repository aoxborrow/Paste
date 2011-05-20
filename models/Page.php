<?php

// page view model
class Page extends Mustache {

	// path to page content, relative to CONTENTPATH
	public $path;

	// default mustache template, relative to TEMPLATEPATH
	// template defined in page variable
	// allow setting this in section variable
	public $template = 'page.mustache';

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

	// child pages, populated by Menu
	public $children = array();

	// methods and properties to exclude when converting to array
	// public $_exclude = array('factory', 'render', 'as_array');


	// constructor sets name and loads data
	public function __construct($name, $path) {

		// set project name
		$this->name = $name;
		$this->path = trim($path, '/');

		// load content data
		$this->_load();

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

	// check if current page or section
	public function current() {

		// get current page and section from controller
		$current_page = Router::instance()->current_page;
		$current_section = Router::instance()->current_section;

		return (($this->name == $current_page AND $this->section == $current_section) OR ($this->is_section AND $this->name == $current_section));

	}
	// TODO: variables: collect all lines within <!-- --> comments, strip empty, strip any commments after # or //
	// TODO: variable name is trim'ed() string before colon
	// TODO: variable data is trim'ed() string after colon
	// load individual content page
	public function _load() {

		if (FALSE === ($html = @file_get_contents(realpath(CONTENTPATH.$this->path)))) {
			return FALSE;
		}

		$dom = DOMDocument::loadHTML($html);

		$this->title = @$dom->getElementsByTagName('h1')->item(0)->nodeValue;

		// set title to name if not set otherwise
		$this->title = (empty($this->title)) ? ucwords($this->name) : $this->title;
		$this->content = $html;

	}


	// convert page object to array, moving methods to properties
	// deprecated, Mustache does this fine
	public function as_array() {

		$page_array = array();

		foreach (get_class_methods(__CLASS__) as $method) {

			// ignore methods defined in exclude and those with an underscore prefix
			if (! in_array($method, $this->_exclude) AND $method[0] !== '_') {

				// convert methods to properties
				$page_array[$method] = $this->$method();

			}
		}

		foreach (get_object_vars($this) as $property => $value) {

			// ignore properties defined in exclude and those with an underscore prefix
			if (! in_array($property, $this->_exclude) AND $property[0] !== '_') {

				$page_array[$property] = $value;

			}

		}

		return $page_array;

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

	public function __get($property) {

		// avoid undefined property errors
		return '';

	}


	public function render() {

		// TODO: put template in section variable
		if ($this->section == 'projects') {

			$this->template = 'project.mustache';

		}

		return parent::render(file_get_contents(realpath(TEMPLATEPATH.$this->template)));

	}

}