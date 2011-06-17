<?php

class template_controller {

	// enable caching
	public $caching = TRUE;

	// content cache key
	public $cache_key;

	// buffered output
	public $output;

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
		if (isset($_GET['nocache']) || ! $this->caching)
			Cache::$lifetime = FALSE;

		// clear cache
		if (isset($_GET['clearcache']))
			Cache::instance()->delete_all();

		// use requested URI as cache key
		$this->cache_key = empty(Pastefolio::$current_uri) ? 'index' : Pastefolio::$controller.'-'.Pastefolio::$current_uri;

		// try to fetch requested URI from cache
		$this->output = Cache::instance()->get($this->cache_key);

		// validate cache and delete if old
		$this->_validate_cache();

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

		// print_r(func_get_args());
		return $this->error_404();

	}

	public function error_404() {

		// disable caching for 404s
		$this->caching = FALSE;

		// send 404 header
		header('HTTP/1.1 404 File Not Found');

		// draw 404 content page if available
		$this->page = Content::find(array('name' => '404'));

	}

	// TODO: validate cache and delete if old. this should be implemented by each controller
	public function _validate_cache() {

		if (! empty($this->output)) {
			// validate cache
			// if invalid, delete tag based on controller
			// Cache::instance()->delete_tag(Pastefolio::$controller);
		}

	}

	// auto render template
	public function _render() {

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// render the template after controller execution
		echo $this->template->render($this->page);

		// store the output buffer
		$this->output = ob_get_clean();

		// store output in cache if enabled
		if ($this->caching)
			Cache::instance()->set($this->cache_key, $this->output, array(Pastefolio::$controller));

	}

}