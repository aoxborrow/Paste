<?php

// default content controller
class content_controller extends template_controller {


	public function __call($method, $args) {

		// decipher content request
		$request = empty(Router::$current_uri) ? array(Content::$index) : explode('/', Router::$current_uri);

		// single level, section is root and page is request
		if (count($request) == 1) {

			$this->current_section = NULL;
			$this->current_page = $request[0];

		// set section and page
		} elseif (count($request) == 2) {

			$this->current_section = $request[0];
			$this->current_page = $request[1];

		} else {

			echo "I don't know how to handle this request:";
			die(print_r($request));

		}

		// TODO: check if index page has content, show it, otherwise redirect to first page
		// redirect to first project
		//Router::redirect('/'.$section.'/index');
		//$this->template->content .= Page::factory($section);

		// ghetto breadcrumb
		$this->template->content = '<p><b>'.(($this->current_section !== NULL) ? $this->current_section.' / ' : '').$this->current_page.'</b></p>';

		$page = Page::factory($this->current_page);

		$this->template->title = $page->title.' - '.$this->template->title;
		$this->template->content .= $page;



	}

	public function all($section) {

		$output = '<h1>All Pages of <b>'.$section.'</b></h1>';

/*
		foreach (Content::load_section($section) as $name) {
			$output .= $name.'<br/>';
		}*/

		$this->template->content = $output;

	}

}