<?php

// template view wrapper, in order to more easily change templating libraries
class Template {

	// view model
	public $page;

	// template contents
	public $_template;

	// cache templates when possible
	protected static $cache = array();

	// template file extension
	protected static $ext = '.mustache';

	// pass template name and optionally a view model into constructor
	public function __construct($template = NULL, $page = NULL) {

		if (! empty($template))
			// load template
			$this->_template = $this->load($template);

		if (! empty($page))
			// assign view model
			$this->page = $page;

	}

	// set main template
	public function set($template) {

		// load template
		$this->_template = $this->load($template);

	}

	// get template file contents
	public function load($template) {

		// no template set
		if (empty($template))
			return;

		// ensure .mustache file extension
		$template = (strstr($template, self::$ext)) ? $template : $template.self::$ext;

		// check template cache
		if (! isset(self::$cache[$template])) {

			// load template file and add to cache
			self::$cache[$template] = file_get_contents(realpath(TEMPLATEPATH.$template));

		}

		return self::$cache[$template];

	}

	// TODO: make this an array of partials that gets combined on render
	// merge one template into another via the {{{content}}} string
	public function partial($partial) {

		$partial = $this->load($partial);

		$this->_template = str_replace('{{{content}}}', $partial, $this->_template);

	}

	// render the template and return the output
	public function render() {

		// instantiate Mustache view
		return (string) new Mustache($this->_template, $this->page);

	}

	// render the template in string conversion
	public function __toString() {

		return $this->render();

	}


}