<?php

// template view wrapper, in order to more easily change templating libraries
class Template {

	// view model
	public $model;

	// template contents
	public $template;

	// cache templates when possible
	protected static $cache = array();

	// template file extension
	protected static $ext = '.mustache';

	// pass template name and optionally a view model into constructor
	public function __construct($template, $model = NULL) {

		// load Mustache class if we haven't already
		if (! class_exists('Mustache', FALSE))
			// using Mustache for templating: https://github.com/bobthecow/mustache.php
			require_once APPPATH.'libraries/Mustache/Mustache.php';

		if (! empty($template))
			// load template
			$this->template = $this->load($template);

		if (! empty($model))
			// assign view model
			$this->model = $model;

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

	// merge one template into another via the {{{content}}} string
	public function combine($sub_template) {

		$sub_template = $this->load($sub_template);

		$this->template = str_replace('{{{content}}}', $sub_template, $this->template);

	}

	// render the template and return the output
	public function render() {

		// instantiate Mustache view
		return (string) new Mustache($this->template, $this->model);

	}

	// render the template in string conversion
	public function __toString() {

		return $this->render();

	}


}