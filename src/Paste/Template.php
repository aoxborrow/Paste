<?php

namespace Paste;

// template view wrapper, in order to easily change templating libraries
class Template {

	// template contents
	public $_template;

	// cache templates when possible
	protected static $cache = array();

	// template file extension
	protected static $ext = '.stache';

	// factory for method chaining. supply optional template name
	public static function factory($template = NULL) {

		// instantiate this class
		$tpl = new Template;

		// load template if supplied
		if (! empty($template))
			$tpl->set($template);

		// return Template instance
		return $tpl;

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
			self::$cache[$template] = file_get_contents(realpath(Paste::$template_path.$template));

		}

		return self::$cache[$template];

	}

	// TODO: make this an array of partials that gets combined on render
	// merge one template into another via the {{{content}}} string
	public function partial($partial) {

		$partial = $this->load($partial);

		$this->_template = str_replace('{{{content}}}', $partial, $this->_template);

	}

	// render the template with supplied page model
	public function render($page = NULL) {

		// a Page model with inherited template and partials
		if ($page instanceof Page) {

			// get defined page template, inherited from parent if necessary
			$page_template = $page->template();

			// setup main page template
			$this->set($page_template);

			// get defined page partial if available
			$page_partial = $page->partial();

			// combine templates if partial defined
			if (! empty($page_partial))
				$this->partial($page_partial);

		}

		// instantiate Mustache view and render template
		return (string) new Mustache($this->_template, $page);

	}

}