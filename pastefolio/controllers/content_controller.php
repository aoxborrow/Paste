<?php

// default content controller
class content_controller extends template_controller {


	public function __call($method, $args) {

		// decipher content request
		$request = empty(Pastefolio::$current_uri) ? array('index') : explode('/', Pastefolio::$current_uri);

		// single level, section is root and page is request
		if (count($request) == 1) {

			$this->current_section = NULL;
			$this->current_page = $request[0];

		// set section and page
		} elseif (count($request) == 2) {

			$this->current_section = $request[0];
			$this->current_page = $request[1];

		// multiple levels deep
		} else {

			echo "I don't know how to handle this request:";
			die(print_r($request));

		}

		// ghetto breadcrumb
		$this->template->content = '<p><b>'.(($this->current_section !== NULL) ? $this->current_section.' / ' : '').$this->current_page.'</b></p>';

		// get requested page from content database
		$page = Page::find(array('section' => $this->current_section, 'name' => $this->current_page));

		// no page found
		if ($page === FALSE) {

			// trigger 404 message
			return $this->error_404();

		// section page configured to redirect to first child
		} elseif ($page->is_section AND $page->redirect == 'first_child') {

			// get first child page name
			$first = array_shift(Page::flat_section($page->name));

			// redirect to first project
			Pastefolio::redirect('/'.$page->name.'/'.$first);

		// page redirect configured
		} elseif (! empty($page->redirect)) {

			// redirect to url
			Pastefolio::redirect($page->redirect);

		} else {


			// ensure .mustache file extension
			$page->template = (strstr($page->template, '.mustache')) ? $page->template : $page->template.'.mustache';

			// get template file contents
			$template = file_get_contents(realpath(TEMPLATEPATH.$page->template));

			// set page title similar to breadcrumbs
			$this->template->title = $page->title.' - '.$this->template->title;

			// passing template and view model to Mustache during runtime, so that we don't store Mustache properties in cache
			$this->template->content = new Mustache($template, $page);

		}

	}

}