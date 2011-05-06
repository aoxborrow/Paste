<?php

class _404_Controller extends Template_Controller {

		
	public function index() {

		header('HTTP/1.1 404 File Not Found');

		$this->template->breadcrumb = 'Page not found!';
		$this->template->content = 'Page not found!';
		
	}
	
}