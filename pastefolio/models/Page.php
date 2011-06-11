<?php

// page model
class Page {

	// page name and link id
	public $name;

	// path to page content
	public $path;

	// page title, used in menu
	public $title;

	// optional page description
	public $description;

	// page content
	public $content;

	// mustache template relative to TEMPLATEPATH
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
	public $is_visible = TRUE;

	// all parent sections
	public $parents = array();

	// child pages, populated by Content library
	public $children = array();

	// takes a content file path and returns a Page model
	public static function factory($path) {

		// instantiate Page model
		$page = new Page;

		// file name without prefix or extension
		$page->name = Content::base_name($path);

		// path without trailing slash
		$page->path = rtrim($path, '/');

		// strip content path off to get parent sections
		$parents = substr($page->path, strlen(CONTENTPATH));

		// parents array is all enclosing sections
		$parents = array_reverse(explode('/', $parents, -1));

		// filter parent sections for base names
		$page->parents = array_map(array('Content', 'base_name'), $parents);

		// TODO: consider changing structure to create is_section files that don't have content, only section vars
		// sections are represented by their index file
		if ($page->name == 'index') {

			$page->is_section = TRUE;

			// if deeper than root section
			if (! empty($page->parents))

				// change name from index to section name and remove section from parents array
				$page->name = array_shift($page->parents);

		}

		// set section from parents array if deeper than root
		$page->section = (empty($page->parents)) ? NULL : $page->parents[0];

		// load file content into model
		$page->load();

		// return loaded page model
		return $page;

	}

	// get root section
	public function root() {

		return Content::section(NULL);

	}

	// display child pages
	public function children() {

		return Content::section($this->name);

	}

	public function parent1() {

		return $this->parents[1];

	}

	public function parent2() {

		return $this->parents[2];

	}

	public function __get($property) {

		// allow accessing parent names DOESN'T WORK!
		if (substr($property, 0, 6) == 'parent') {

			// get number from property name, ie. parent0, parent1, etc.
			$parent_num = (int) substr($property, 7, 1);

			// return parent name
			return (isset($this->parents[$parent_num])) ? $this->parents[$parent_num] : '';

		}

		// otherwise avoid undefined property errors
		return '';

	}

	// TODO: strip any commments after # or //
	// load individual content page
	public function load() {

		if (($html = @file_get_contents(realpath($this->path))) !== FALSE) {

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

			// convert booleans to native
			foreach ($vars as $key => $value) {
				if (strtolower($value) === "false" OR $value === '0') {
					$value = FALSE;
				} elseif (strtolower($value) === "true" OR $value === '1') {
					$value = TRUE;
				}
				$this->$key = $value;
			}

			// set title to name if not set otherwise
			$this->title = (empty($this->title)) ? ucwords(str_replace('_', ' ', $this->name)) : $this->title;
			$this->content = $html;
			// debug page variables
			$this->content .= "<pre>".htmlentities(print_r($vars, TRUE)).'</pre>';

		}
	}

	// check if current page or section
	public function current() {

		// get current page and section from controller
		$current_page = Pastefolio::instance()->current_page;
		$current_section = Pastefolio::instance()->current_section;

		// return (($this->name == $current_page AND $this->section == $current_section) OR ($this->is_section AND $this->name == $current_section));
		return ($this->name == $current_page AND $this->section == $current_section);

	}


	public function next_url() {

		// start with current section if exists
		$url = (empty($this->section)) ? '/' : '/'.$this->section.'/';

		// get next page in section
		$next = $this->relative_page(1);

		// cycle to first page if last in section
		$url .= ($next === FALSE) ? $this->first_page() : $next;

		return $url;

	}

	public function prev_url() {

		// start with current section if exists
		$url = (empty($this->section)) ? '/' : '/'.$this->section.'/';

		// get previous page in section
		$prev = $this->relative_page(-1);

		// cycle to last page if first in section
		$url .= ($prev === FALSE) ? $this->last_page() : $prev;

		return $url;

	}

	// returns project name relative to specified project
	public function relative_page($offset = 0) {

		// create page map from current section
		$section = Content::flat_section($this->section);

		// find current key
		$current_page_index = array_search($this->name, $section);

		// return desired offset, if in array
		if (isset($section[$current_page_index + $offset])) {
			return $section[$current_page_index + $offset];
		}

		// otherwise return false
		return FALSE;
	}


	public function first_page() {

		// create page map from current section
		$section = Content::flat_section($this->section);

		// get first item of current section
		return array_shift($section);

	}

	public function last_page() {

		// create page map from current section
		$section = Content::flat_section($this->section);

		// get last item of current section
		return array_pop($section);

	}


}