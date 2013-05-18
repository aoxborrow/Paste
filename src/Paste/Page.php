<?php

namespace Paste;

// page model
class Page {

	// page name and link id
	public $name;

	// path to page content
	public $path;
	
	// page successfully loaded
	public $loaded = FALSE;

	// modified time (unix mtime)
	public $mtime;

	// page title, used in menu
	public $title;

	// optional page description
	public $description;

	// page content
	public $content;

	// mustache template name
	public $template;

	// redirect URL for creating aliases
	public $redirect;

	// thumbnail for gallery display
	public $thumb;

	// parent section
	public $section;

	// page is a section index
	public $is_section = FALSE;

	// visible in menu
	public $visible = TRUE;

	// all parent sections
	public $parents = array();

	// cache templates when possible
	public static $template_cache = array();

	// template file extension
	public static $template_ext = '.stache';

	// template directory relative to app path
	public static $template_dir = 'templates';

	// content "database"
	public static $db;

	// content file extension
	public static $content_ext = '.html';
	
	// content directory relative to app path
	public static $content_dir = 'content';
	
	// current page data model
	public static $current_page;
	
	// decipher request and render content page
	public static function get($uri = NULL) {
		
		// trim slashes
		$uri = trim($uri, '/');
		
		// decipher content request
		$request = empty($uri) ? array('index') : explode('/', $uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$page = end($request);
		
		// get requested page from content database
		self::$current_page = self::find(array('section' => $section, 'name' => $page));
		
		// no page found
		if (self::$current_page === FALSE) {

			// send 404 header
			header('HTTP/1.1 404 File Not Found');

			// draw 404 content if available
			self::$current_page = self::find(array('name' => '404'));
			
			// if no 404 content available, do somethin' sensible
			if (self::$current_page === FALSE) {

				// simple 404 page
				self::$current_page = new Page;
				self::$current_page->title = 'Error 404 - File Not Found';
				self::$current_page->content = '<h1>Error 404 - File Not Found</h1>';
				
			}

		// page redirect configured
		} elseif (! empty(self::$current_page->redirect)) {

			// redirect to url
			return Paste::redirect(self::$current_page->url());

		}
		
		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');
		
		// page wasn't loaded
		if (! self::$current_page->loaded)
			die(print_r(self::$current_page));
		
		// render the template 
		echo self::$current_page->render();
		
	}
	

	// takes a content file path and returns a Page model
	public static function factory($path) {

		// instantiate Page model
		$page = new Page;

		// file name without prefix or extension
		$page->name = self::base_name($path);

		// file modified time
		$page->mtime = filemtime($path);

		// path without trailing slash
		$page->path = rtrim($path, '/');

		// strip content path off to get parent sections
		$parents = substr($page->path, strlen(Paste::$path.self::$content_dir.'/'));

		// parents array is all enclosing sections
		$parents = array_reverse(explode('/', $parents, -1));

		// filter parent sections for base names
		$page->parents = array_map(array('Paste\\Page', 'base_name'), $parents);

		// sections are represented by their index file
		if ($page->name == 'index') {

			$page->is_section = TRUE;

			// if deeper than root section (root section remains index)
			if (! empty($page->parents)) {
				
				// change name from index to section name and remove section from parents array
				$page->name = array_shift($page->parents);

			}

		}

		// set section from parents array if deeper than root
		$page->section = (empty($page->parents)) ? NULL : $page->parents[0];

		// setup parent1, parent2, etc. properties for use in templates
		foreach ($page->parents as $num => $parent)
			$page->{'parent'.$num} = $parent;

		// load file content into model
		$page->load();

		// return loaded page model
		return $page;

	}

	// build full URL based on parent sections, or use defined redirect
	public function url($base = '/') {

		// section page configured to redirect to first child
		if ($this->is_section AND $this->redirect == 'first_child') {

			// get first child page name
			$first = $this->first_child();

			// return first child url
			return $first->url();

		// redirect configured
		} elseif (! empty($this->redirect)) {

			return $this->redirect;

		} else {

			// iterate parent sections in reverse
			foreach (array_reverse($this->parents) as $parent)
				$base .= $parent.'/';

			// add page name
			return $base.$this->name;

		}

	}

	// check if current page or section
	public function is_current() {

		/*
		// get current URI, trim slashes
		$uri = trim(Paste::instance()->uri, '/');
		
		// decipher content request
		$request = empty($uri) ? array('index') : explode('/', $uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$current_section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$current_page = end($request);
		*/

		// get current page and section from controller
		// $current_page = Controller::instance()->current_page;
		// $current_section = Controller::instance()->current_section;
		
		$current_page = Page::$current_page->name;
		$current_section = Page::$current_page->section;
		
		// echo 'current_page: '.$current_page."<br>";
		// echo 'current_section: '.$current_section."<br>";
		
		if (Page::$current_page->is_section) {
			// if a section, don't allow parent section to be current()
			return ($this->name == $current_page AND $this->section == $current_section);
		} else {
			// if a regular page, allow parent section to be current() 
			// TODO:: change this in template to check for section() or parent()->name
			return (($this->name == $current_page AND $this->section == $current_section) OR ($this->is_section AND $this->name == $current_section));
		}


	}

	// load individual content page, process variables
	public function load() {

		if (($html = @file_get_contents(realpath($this->path))) !== FALSE) {

			// process variables
			$vars = $this->_variables($html);

			// assign vars to current model
			foreach ($vars as $key => $value)
				$this->$key = $value;

			// set title to name if not set otherwise
			$this->title = (empty($this->title)) ? ucwords(str_replace('_', ' ', $this->name)) : $this->title;

			// assign entire html to content property
			$this->content = $html;

			// add page variables for debugging
			// $this->content .= "<pre>".htmlentities(print_r($vars, TRUE)).'</pre>';
			
			// page is loaded
			$this->loaded = TRUE;

		}
	}

	// process content for embedded variables
	protected function _variables($html) {

		// credit to Ben Blank: http://stackoverflow.com/questions/441404/regular-expression-to-find-and-replace-the-content-of-html-comment-tags/441462#441462
		$regexp = '/<!--((?:[^-]+|-(?!->))*)-->/Ui';
		preg_match_all($regexp, $html, $comments);

		// split comments on newline
		$lines = array();
		foreach ($comments[1] as $comment) {
			$var_lines = explode("\n", trim($comment));
			$lines = array_merge($lines, $var_lines);
		}

		// split lines on colon and assign to key/value
		$vars = array();
		foreach ($lines as $line) {
			$parts = explode(":", $line, 2);
			if (count($parts) == 2) {
				$vars[trim($parts[0])] = trim($parts[1]);
			}
		}

		// assign variables in content
		foreach ($vars as $key => $value) {

			// convert booleans to native
			if (strtolower($value) === "false" OR $value === '0') {

				$value = FALSE;

			// convert booleans to native
			} elseif (strtolower($value) === "true" OR $value === '1') {

				$value = TRUE;

			// strip any comments from	variables, except redirect
			} elseif ($key !== 'redirect' AND strpos($value, '//')) {

				$value = substr($value, 0, strpos($value, '//'));

			}

			$vars[$key] = $value;
		}

		return $vars;

	}

	public static function debug() {
		
		echo 'Debug:<br>';
		print_r(Page::find_section(NULL));
		
	}

	// get parent section
	public function parent() {

		// root section parent is 'index', rest are section name
		$parent = ($this->section == NULL) ? 'index' : $this->section;
		// $parent = $this->section;

		return Page::find(array('name' => $parent));

	}

	// return all visible child pages
	public function children($terms = array()) {

		// add optional search terms
		$terms = array_merge($terms, array('section' => $this->name, 'visible' => TRUE));

		return Page::find_all($terms);

	}

	public function first_child() {

		// get visible child pages
		$children = $this->children();

		// get first of child pages
		return array_shift($children);

	}

	public function last_child() {

		// get visible child pages
		$children = $this->children();

		// get first of child pages
		return array_shift($children);

	}

	// return all visible siblings
	public function siblings($terms = array()) {

		// add optional search terms
		$terms = array_merge($terms, array('section' => $this->section, 'visible' => TRUE));

		return Page::find_all($terms);

	}

	public function first_sibling() {

		// get visible siblings
		$siblings = $this->siblings();

		// get first sibling in section
		return array_shift($siblings);

	}

	public function last_sibling() {

		// get visible siblings
		$siblings = $this->siblings();

		// get last sibling in section
		return array_pop($siblings);

	}

	public function next_sibling() {

		// get next page in section
		$next = $this->_relative_page(1);

		// cycle to first page if last in section
		return ($next === FALSE) ? $this->first_sibling()->url() : $next->url();

	}

	public function prev_sibling() {

		// get previous page in section
		$prev = $this->_relative_page(-1);

		// cycle to last page if first in section
		return ($prev === FALSE) ? $this->last_sibling()->url() : $prev->url();
	}

	// returns page relative to current
	public function _relative_page($offset = 0) {

		// create page map from current section
		$section = Page::find_names(array('section' => $this->section, 'visible' => TRUE));

		// find current key
		$current_page_index = array_search($this->name, $section);

		// check desired offset
		if (isset($section[$current_page_index + $offset])) {

			// get relative page name
			$relative_page = $section[$current_page_index + $offset];

			// return relative page model
			return Page::find(array('name' => $relative_page));

		}

		// otherwise return false
		return FALSE;
	}
	
	// get property from parent section
	// TODO: use _inheritable array
	public function _inherit($var) {

		if (empty($this->$var)) {

			// get parent section
			$parent = $this->parent();

			// assign parent property to current page
			$this->$var = $parent->_inherit($var);

		}

		return $this->$var;

	}
	
	public function template() {
		
		// no parents and no template set, use base_template
		// if (empty($this->parents) AND empty($this->template))
			// return self::$base_template;

		// return $this->template;
		return $this->_inherit('template');

	}
	
	// get template file contents
	public static function load_template($template) {

		// no template set
		if (empty($template))
			return;

		// ensure correct file extension
		$template = (strstr($template, self::$template_ext)) ? $template : $template.self::$template_ext;

		// check template cache
		if (! isset(self::$template_cache[$template])) {
			
			// directory where content files are stored
			$template_path = Paste::$path.self::$template_dir.'/';

			// load template file and add to cache
			self::$template_cache[$template] = file_get_contents(realpath($template_path.$template));

		}

		return self::$template_cache[$template];

	}
	
	public function menu() {

		return Page::find_section(NULL);
		
	}

	// render the page with templates
	public function render() {
		
		// directory where content files are stored
		$template_path = Paste::$path.self::$template_dir.'/';
		
		// TODO: instantiate engine in constructor, use FilesystemLoader
		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_StringLoader,
			'partials_loader' => new \Mustache_Loader_FilesystemLoader($template_path, array('extension' => ".stache")),
		));

		// get defined page template, inherited from parent if necessary
		$page_template = $this->template();
		
		// echo 'page_template: '.$page_template.'<br>';

		// page has a template
		if (! empty($page_template)) {

			// load template
			$template = self::load_template($page_template);
		
			$tpl = $mustache->loadTemplate($template);
			$content = $tpl->render($this);
			
			$this->content = $content;
			
		}

		// get parent section
		$parent = $this->parent();
		
		
		if ($parent) {
			
			$parent = $parent->parent();
			// echo 'parent: '.$parent->name.'<br>';

			$parent_template = $parent->template();
		}
		
		$parent_template = $parent->template();
		// echo 'parent_template: '.$parent_template.'<br>';
		
		if (! empty($parent_template) AND $parent_template !== $page_template) {
		
			$parent_template = self::load_template($parent_template);
			
			$tpl = $mustache->loadTemplate($parent_template);
			return $tpl->render($this);
		
		} else {
			
			return $this->content;
			
		}
	}
	

	// load content database
	public static function content_load() {
		
		// traverse content directory and load all content
		if (empty(self::$db)) {
			
			// directory where content files are stored
			$content_path = Paste::$path.self::$content_dir.'/';
			
			// load root and all child sections
			self::$db = self::load_section($content_path);
			
		}
		
	}

	// retrieve single page by properties
	public static function find($terms) {
		
		$pages = self::find_all($terms);

		return (empty($pages)) ? FALSE : current($pages);

	}

	// filter and return pages by properties
	public static function find_all($terms) {
		
		// ensure we have content loaded
		self::content_load();

		$pages = array();

		foreach (self::$db as $page) {

			foreach ($terms as $property => $value) {

				if ($page->$property !== $value)
					// skip to next page if property doesn't match
					continue 2;

			}

			// clone the page object so we don't alter original
			$pages[] = clone $page;

		}

		return $pages;

	}

	// returns page names in a flat array
	public static function find_names($terms) {

		$pages = array();

		foreach (self::find_all($terms) as $page) {

			$pages[] = $page->name;

		}

		return $pages;

	}

	// get section child pages
	public static function find_section($section) {

		return self::find_all(array('section' => $section));

	}

	// recursively load sections of content
	public static function load_section($path) {

		$pages = array();

		foreach (self::list_path($path) as $file) {
			
			// sub directory
			if (is_dir($path.$file))
				$pages = array_merge($pages, self::load_section($path.$file.'/'));

			// content file with proper extension
			if (is_file($path.$file) AND strpos($file, self::$content_ext))
				$pages[] = Page::factory($path.$file);

		}
		
		return $pages;

	}

	// return directory list
	public static function list_path($path) {

		$files = array();

		if (($handle = opendir($path)) === FALSE)
			return $files;

		while (($file = readdir($handle)) !== FALSE) {

			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

				$files[] = $file;

			}
		}

		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		usort($files, 'strnatcasecmp');

		// return sorted array (filenames => basenames)
		return $files;

	}

	// get base filename without sorting prefix or extension
	public static function base_name($file) {

		// get file name without content extension
		$name = basename($file, self::$content_ext);

		// base name is everything after intial period if one exists
		$prefix = strpos($name, '.');

		// strip prefix and return cleaned name
		return ($prefix) ? substr($name, $prefix + 1) : $name;

	}
	
	
	

}
