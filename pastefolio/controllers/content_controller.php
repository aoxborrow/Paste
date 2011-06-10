<?php

// default content controller
class content_controller extends template_controller {

	public function __call($method, $args) {

		// decipher content request
		$request = empty(Pastefolio::$current_uri) ? array('index') : explode('/', Pastefolio::$current_uri);

		// single level, section is root and page is full request
		if (count($request) == 1) {

			$this->current_section = NULL;
			$this->current_page = $request[0];

		// set section and page
		} elseif (count($request) == 2) {

			$this->current_section = $request[0];
			$this->current_page = $request[1];

		// set sub section and page
		} elseif (count($request) == 3) {

			$this->current_section = $request[1];
			$this->current_page = $request[2];

		// sub sub section
		} elseif (count($request) == 4) {

			$this->current_section = $request[2];
			$this->current_page = $request[3];


		} else {

			echo "I don't know how to handle this request:";
			die(print_r($request));

		}

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

			if ($page->section == 'projects')
				$page->template = 'project';

			// combine templates if available
			if (! empty($page->template))
				$this->template->combine($page->template);

			$this->template->model = $page;

		}

	}

}