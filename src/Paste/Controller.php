<?php

namespace Paste;

class Controller {

	// page data model
	public $page;

	// template view model
	public $template;

	// current section
	public $current_section;

	// current page
	public $current_page;
	
	// singleton instance
	public static $instance;

	// decipher request and render content page
	public function __construct() {}
		
		
	public function run() {
		
		// get current URI, trim slashes
		$uri = trim(Paste::instance()->uri, '/');
		
		// decipher content request
		$request = empty($uri) ? array('index') : explode('/', $uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$this->current_section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$this->current_page = end($request);
		
		// get requested page from content database
		$this->page = Content::find(array('section' => $this->current_section, 'name' => $this->current_page));
		
		// setup template instance
		$this->template = new Template;

		// no page found
		if ($this->page === FALSE) {

			// send 404 header
			header('HTTP/1.1 404 File Not Found');

			// draw 404 content if available
			$this->page = Content::find(array('name' => '404'));
			
			// if no 404 content available, do somethin sensible
			if ($this->page === FALSE) {

				// simple 404 page
				$this->page = new Page;
				$this->page->title = 'Error 404 - File Not Found';
				$this->page->content = '<h1>Error 404 - File Not Found</h1>';
				
			}

		// page redirect configured
		} elseif (! empty($this->page->redirect)) {

			// redirect to url
			return Paste::redirect($this->page->url());

		}
		
		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');
		
		// page wasn't loaded
		if (! $this->page->loaded)
			die(print_r($this->page));
		
		// render the template 
		echo $this->template->render($this->page);
		
	}
	
	// singleton instance
	public static function instance() {

		// use existing instance
		if (! isset(self::$instance)) {

			// create a new instance
			self::$instance = new Controller;
		}

		// return instance
		return self::$instance;

	}
}