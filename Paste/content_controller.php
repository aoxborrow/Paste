<?php

class content_controller {

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

		// current_section is controller name by default
		$this->current_section = Pastefolio::$controller;

		// current_page is controller method by default
		$this->current_page = Pastefolio::$method;

		// setup template instance
		$this->template = new Template;

		// empty page model
		$this->page = new Page;

		// start output buffering
		ob_start();

	}

	public function __call($method, $arguments) {
		
		// init Content database
		// Content::init();

		// check for cached content before executing
		if ($this->_valid_cache())
			return;

		// decipher content request
		$request = empty(Pastefolio::$current_uri) ? array('index') : explode('/', Pastefolio::$current_uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$this->current_section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$this->current_page = end($request);
		
		// get requested page from content database
		$this->page = Content::find(array('section' => $this->current_section, 'name' => $this->current_page));

		// no page found
		if ($this->page === FALSE) {

			// trigger 404 message
			return $this->_404();

		// page redirect configured
		} elseif (! empty($this->page->redirect)) {

			// redirect to url
			Pastefolio::redirect($this->page->url());

		}

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

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// echo buffered output
		echo $this->output;

	}

}