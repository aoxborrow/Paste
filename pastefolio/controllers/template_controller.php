<?php

class template_controller {

	// buffered output
	public $output;

	// content cache key
	public $cache_key;

	// page data model
	public $page;

	// template view model
	public $template;

	// current section
	public $current_section;

	// current page
	public $current_page;


	public function __construct() {

		// set router instance to controller
		if (Pastefolio::$instance == NULL)
			Pastefolio::$instance = $this;

		// disable cache
		if (isset($_GET['nocache']))
			Cache::$lifetime = FALSE;

		// clear cache
		if (isset($_GET['clearcache']))
			Cache::instance()->delete_all();

		// use requested URI as cache key
		$this->cache_key = empty(Pastefolio::$current_uri) ? 'index' : Pastefolio::$controller.'_'.Pastefolio::$current_uri;

		// try to fetch requested URI from cache
		$this->output = Cache::instance()->get($this->cache_key);

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// start output buffering
		ob_start();

		// current_section is controller name by default
		$this->current_section = Pastefolio::$controller;

		// current_page is controller method by default
		$this->current_page = Pastefolio::$method;

		// setup template model
		$this->template = new Template;

		// empty page model
		$this->page = new Page;

	}

	public function __call($method, $args) {

		// controller method not found
		return $this->_404();

	}

	// validate cache and delete if old. this method should be implemented by each controller that uses caching
	public function _valid_cache() {

		return ! empty($this->output);

	}

	public function _404() {

		// disable caching for 404s
		Cache::$lifetime = FALSE;

		// send 404 header
		header('HTTP/1.1 404 File Not Found');

		// draw 404 content if available
		$this->page = Content::find(array('name' => '404'));

		// if no 404 content available, just echo simple error message
		if (empty($this->page))
			echo '<h1>404 - File Not Found</h1>';

	}

	// auto render template
	public function _render() {

		// no cached version available, render template
		if (empty($this->output)) {

			// render the template after controller execution
			echo $this->template->render($this->page);

			// store the output buffer
			$this->output = ob_get_contents();

			// store output in cache if enabled
			Cache::instance()->set($this->cache_key, $this->output);

		}

		// end output buffering and clear buffer
		ob_end_clean();

		// echo buffered output
		echo $this->output;

	}

}