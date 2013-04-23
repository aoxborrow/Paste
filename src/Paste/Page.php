<?php

namespace Paste;

// page model
class Page extends Mustache{

	// page name and link id
	public $name;

	// path to page content
	public $path;

	// modified time (unix mtime)
	public $mtime;

	// page title, used in menu
	public $title;

	// optional page description
	public $description;

	// page content
	public $content;

	// mustache template relative to template_path
	public $template;

	// partial mustache template, gets folded into parent section template
	public $partial;

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

	// these vars cascade down through child pages
	// public $_inherit = array('template', 'partial');

	// takes a content file path and returns a Page model
	public static function factory($path) {

		// instantiate Page model
		$page = new Page;

		// file name without prefix or extension
		$page->name = Content::base_name($path);

		// file modified time
		$page->mtime = filemtime($path);

		// path without trailing slash
		$page->path = rtrim($path, '/');

		// strip content path off to get parent sections
		$parents = substr($page->path, strlen(Paste::$content_path));

		// parents array is all enclosing sections
		$parents = array_reverse(explode('/', $parents, -1));

		// filter parent sections for base names
		$page->parents = array_map('Paste\Content::base_name', $parents);

		// TODO: consider changing structure to create is_section files that don't have content, only section vars
		// sections are represented by their index file
		if ($page->name == 'index') {

			$page->is_section = TRUE;

			// if deeper than root section (root section remains index)
			if (! empty($page->parents))

				// change name from index to section name and remove section from parents array
				$page->name = array_shift($page->parents);

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
	public function current_old() {

		// if current page URL matches request url
		return ltrim($this->url(), '/') == Pastefolio::$current_uri;

	}

	// check if current page or section
	public function current() {

		// get current page and section from controller
		$current_page = Pastefolio::instance()->current_page;
		$current_section = Pastefolio::instance()->current_section;

		return (($this->name == $current_page AND $this->section == $current_section) OR ($this->is_section AND $this->name == $current_section));
		// return ($this->name == $current_page AND $this->section == $current_section);

	}

	public function template() {

		return $this->_inherit('template');

	}

	public function partial() {

		return $this->_inherit('partial');

	}

	// get property from parent section
	public function _inherit($var) {

		if (empty($this->$var)) {

			// get parent section
			$parent = $this->parent();

			// assign parent property to current page
			$this->$var = $parent->_inherit($var);

		}

		return $this->$var;

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


	// get root section
	public function root() {

		return Content::section(NULL);

	}

	// get parent section
	public function parent() {

		// root section parent is 'index', rest are section name
		$parent = ($this->section == NULL) ? 'index' : $this->section;

		return Content::find(array('name' => $parent));

	}

	// return all visible child pages
	public function children($terms = array()) {

		// add optional search terms
		$terms = array_merge($terms, array('section' => $this->name, 'is_visible' => TRUE));

		return Content::find_all($terms);

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
		$terms = array_merge($terms, array('section' => $this->section, 'is_visible' => TRUE));

		return Content::find_all($terms);

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
		$section = Content::find_names(array('section' => $this->section, 'is_visible' => TRUE));

		// find current key
		$current_page_index = array_search($this->name, $section);

		// check desired offset
		if (isset($section[$current_page_index + $offset])) {

			// get relative page name
			$relative_page = $section[$current_page_index + $offset];

			// return relative page model
			return Content::find(array('name' => $relative_page));

		}

		// otherwise return false
		return FALSE;
	}

}