<?php

// catch-all content controller
class content_controller extends template_controller {

	public function __call($method, $arguments) {

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

		// section page configured to redirect to first child
		} elseif ($this->page->is_section AND $this->page->redirect == 'first_child') {

			// get first child page name
			$first = $this->page->first_child();

			// redirect to first child url
			Pastefolio::redirect($first->url());

		// page redirect configured
		} elseif (! empty($this->page->redirect)) {

			// redirect to url
			Pastefolio::redirect($this->page->redirect);

		}

	}

}