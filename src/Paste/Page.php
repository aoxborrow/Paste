<?php

namespace Paste;

// page model
class Page {

	// page successfully loaded
	public $loaded = FALSE;

	// page name and link id
	public $name;

	// path to page content
	public $path;
	
	// page title, used in menu
	public $title;

	// page content
	public $content;

	// mustache template name
	public $template;

	// redirect URL for creating aliases
	public $redirect;

	// parent section
	public $parent;

	// page is a section index
	public $is_parent = FALSE;

	// visible in menu
	public $visible = TRUE;

	// template file extension
	public static $template_ext = '.stache';

	// template directory relative to app path
	public static $template_dir = 'templates';

	// content file extension
	public static $content_ext = '.html';
	
	// content directory relative to app path
	public static $content_dir = 'content';
	
	// current page data model
	public static $current;
	
	// content "database"
	public static $db;
	
	// placeholder for now
	public function __construct() {} 
	
	// decipher request and render content page
	public static function get($uri = NULL) {
		
		// trim slashes
		$uri = trim($uri, '/');
		
		// decipher content request
		if (empty($uri)) {
			
			// root section
			$parent = FALSE;
			$page = 'index';
			
		} else {
			
			// split up request
			$request = explode('/', $uri);

			// current section is 2nd to last argument (ie. parent3/parent2/parent/page) or 'index' if root section
			$parent = (count($request) <= 1) ? 'index' : $request[count($request) - 2];

			// current page is always last argument of request
			$page = end($request);
			
		}
		
		// get requested page from content database
		self::$current = self::page(array('parent' => $parent, 'name' => $page));
		
		// no page found
		if (self::$current === FALSE OR self::$current->loaded === FALSE) {

			// send 404 header
			header('HTTP/1.1 404 File Not Found');

			// draw 404 content if available
			self::$current = self::page(array('name' => '404'));
			
			// if no 404 content available, do somethin' sensible
			if (self::$current === FALSE) {

				// simple 404 page
				self::$current = new Page;
				self::$current->title = 'Error 404 - File Not Found';
				self::$current->content = '<h1>Error 404 - File Not Found</h1>';
				
			}

		// page redirect configured
		} elseif (! empty(self::$current->redirect)) {

			// redirect to url
			return Paste::redirect(self::$current->url());

		}
		
		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');
		
		// render the template 
		echo self::$current->render();

	}
	

	// takes a content file path and returns a Page model
	public static function factory($path) {
		
		// instantiate Page model
		$page = new Page;
		
		// path without trailing slash
		$page->path = rtrim($path, '/');

		// file name without prefix or extension
		$page->name = self::base_name($page->path);

		// strip content path off to get URL
		$url = substr($page->path, strlen(Paste::$path.self::$content_dir.'/'));

		// parents array is all enclosing sections
		$parents = explode('/', $url, -1);
		
		// filter parent sections for base names
		$parents = array_map('Paste\Page::base_name', $parents);
		
		// parent sections are represented by their index file
		$page->is_parent = ($page->name == 'index');

		// change name from index to parent name and remove from parents array
		if ($page->is_parent AND ! empty($parents))
			$page->name = array_pop($parents);

		// set parent for non-index pages, for root content it's "index"
		$page->parent = empty($parents) ? 'index' : end($parents);
		
		// the root section has no parents! like batman
		if ($page->name == 'index')
			$page->parent = FALSE;
		
		// build URL
		$page->url = '/';
				
		// add parents to URL
		if (! empty($parents)) 
			// iterate parents in reverse
			foreach ($parents as $parent)
				$page->url .= $parent.'/';
		
		// add page name to URL
		$page->url .= $page->name;

		// load file content into model
		$page->load();

		// return loaded page model
		return $page;

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
	public function _variables($html) {

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
	
	// static function that gets the root index menu
	public static function index() {

		// get index section
		$index = self::page(array('parent' => FALSE));
		
		// build full menu from index section
		$menu = $index->menu();
		
		// if visible show root index page in menu, otherwise just return children
		return ($index->visible) ? $menu : $menu['children'];

	}
	
	// menu items relative to current page
	// returns simple menu heirarchy with:
	// url, title, current, parent, children
	public function menu() {
		
		// build menu basics
		$menu_item = array(
			'url' => $this->url(),
			'title' => $this->title,
			'current' => $this->current(),
			'parent' => $this->is_parent,
			'children' => FALSE,
		);
		
		// add child menu items
		if ($this->is_parent) {
			
			// add to children key
			$menu_item['children'] = array();
			
			// get all visible child pages who call this one mommy
			$children = self::pages(array('parent' => $this->name, 'visible' => TRUE));

			// add child menu items recursively
			foreach ($children as $child)
				$menu_item['children'][] = $child->menu();
		}

		// return 
		return $menu_item;

	}
	
	// build full URL based on parents, or use defined redirect
	public function url() {

		// no redirect configured, use URL set in constructor
		if (empty($this->redirect))
			return $this->url;
			
		// parent configured to redirect to first child
		if ($this->is_parent AND $this->redirect == 'first_child')
			// get first child page URL
			return $this->_relative('first', $this->name)->url();
			
		// otherwise just a URL redirect
		return $this->redirect;

	}
	
	// check if current page
	public function current() {
		
		// no current page?!
		if (empty(self::$current))
			return FALSE;

		// simple enough -- the current page
		if ($this === self::$current)
			return TRUE;
		
		// this is the current section parent! (and the current page is not a section, ie. don't highlight two sections)
		if (! self::$current->is_parent AND $this->name === self::$current->parent)
			return TRUE;
		
		// no go
		return FALSE;
		
	}

	// get all parents in an array
	public function parents() {

		// start the recursion
		$parent = $this;

		// init
		$parents = array();

		// add parents while possible
		while ($parent) {
			
			// get parent page
			$parent = self::page(array('name' => $parent->parent));

			// add to list
			$parents[] = $parent;

		}
		
		// reverse list of parents and return
		return array_reverse($parents);

	}

	public function next() {

		// get next page in section
		$next = $this->_relative(1);

		// cycle to first page if last in section
		return ($next === FALSE) ? $this->_relative('first')->url() : $next->url();

	}

	public function prev() {

		// get previous page in section
		$prev = $this->_relative(-1);

		// cycle to last page if first in section
		return ($prev === FALSE) ? $this->_relative('last')->url() : $prev->url();
	}

	// returns relative page, uses current section by default
	public function _relative($offset = 0, $parent = NULL) {
		
		// use current section if not supplied
		if ($parent === NULL)
			$parent = $this->parent;

		// create page map from current section
		$section = self::pages(array('parent' => $parent, 'visible' => TRUE));
		
		// build simple array of names
		$siblings = array();
		foreach ($section as $sibling)
			$siblings[] = $sibling->name;
		
		// numeric offset
		if (is_numeric($offset)) {
		
			// find current key
			$current_index = array_search($this->name, $siblings);

			// desired offset exists, use that
			$relative_page = isset($siblings[$current_index + $offset]) ? $siblings[$current_index + $offset] : FALSE;

		// first sibling
		} elseif ($offset == 'first') {
			
			$relative_page = array_shift($siblings);
			
		// last sibling	
		} elseif ($offset == 'last') {
			
			$relative_page = array_pop($siblings);

		}
		
		// return relative page model or FALSE if it doesn't exist
		return empty($relative_page) ? FALSE : self::page(array('name' => $relative_page));

	}
	
	// return array of cascading templates
	public function templates() {

		// init array
		$templates = array();

		// iterate over containing parents
		foreach ($this->parents() as $parent) {
			
			// add parent template if any
			if (! empty($parent->template))
				$templates[] = $parent->template;

		}
		
		// add page template -- order from parents is already reversed, so this is last
		if (! empty($this->template))
			$templates[] = $this->template;
		
		// remove any duplicates and return array
		return array_unique($templates);

	}
	
	// render the page with templates
	public function render() {
		
		// directory where content files are stored
		$templates_path = Paste::$path.self::$template_dir.'/';
		
		// TODO: instantiate engine in constructor, use FilesystemLoader
		// TODO: setup cache folder in Paste
		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_StringLoader,
			'partials_loader' => new \Mustache_Loader_FilesystemLoader($templates_path, array('extension' => ".stache")),
			// 'cache' => Paste::$path.'cache',
		));

		// placeholder
		$template = '{{{content}}}';

		// iterate over templates and merge together
		foreach ($this->templates() as $parent_template) {
			
			// directory where template files are stored - template name - file extension
			$template_path = Paste::$path.self::$template_dir.'/'.$parent_template.self::$template_ext;

			// load template file 
			$parent_template = file_get_contents(realpath($template_path));
			
			// merge one template into another via the {{{content}}} string
			$template = str_replace('{{{content}}}', $parent_template, $template);

		}
		
		$template = $mustache->loadTemplate($template);
		return $template->render($this);

	}
	
	// find a single page by properties
	public static function page($terms) {
		
		// get all pages that match
		$pages = self::pages($terms);
		
		// only return single result
		return empty($pages) ? FALSE : array_shift($pages);

	}
	
	// filter and return pages by properties
	// when $first, only return first result, not in an array
	public static function pages($terms) {
		
		// ensure we have content DB loaded
		if (empty(self::$db)) {
			
			// directory where content files are stored
			$content_path = Paste::$path.self::$content_dir.'/';
			
			// traverse content directory and load all content
			self::$db = self::load_section($content_path);
			
		}

		// store pages here
		$pages = array();

		// iterage pages, return by reference
		foreach (self::$db as &$page) {

			// iterate over search terms
			foreach ($terms as $property => $value)
				// skip to next page if property doesn't match
				if ($page->$property !== $value)
					continue 2;

			// add to pages
			$pages[] = $page;

		}
		
		// return FALSE if no pages found
		return empty($pages) ? FALSE : $pages;

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
				$pages[] = self::factory($path.$file);

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
		return ($prefix = strpos($name, '.')) ? substr($name, $prefix + 1) : $name;

	}
	
	public static function debug() {
		
		echo 'Debug:<br>';
		
		$root = self::page(array('parent' => FALSE));
		
		echo '<pre>';
		print_r($root->menu());
		echo '</pre>';
		
	}
	

}
