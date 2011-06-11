<?php

// catch-all content controller
class content_controller extends template_controller {

	public function __call($method, $args) {

		// decipher content request
		$request = empty(Pastefolio::$current_uri) ? array('index') : explode('/', Pastefolio::$current_uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$this->current_section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$this->current_page = end($request);

		// get requested page from content database
		$page = Content::find(array('section' => $this->current_section, 'name' => $this->current_page));

		// no page found
		if ($page === FALSE) {

			// trigger 404 message
			return $this->error_404();

		// section page configured to redirect to first child
		} elseif ($page->is_section AND $page->redirect == 'first_child') {

			// get first child page name
			$first = $page->first_child();

			// redirect to first child url
			Pastefolio::redirect($first->url());

		// page redirect configured
		} elseif (! empty($page->redirect)) {

			// redirect to url
			Pastefolio::redirect($page->redirect);

		} else {

			$tpl = $page->template();

			$this->template->set($tpl);

			// echo 'template: '.$tpl.'<br/>';

			$partial = $page->partial();

			// echo 'partial: '.$partial.'<br/>';

			// combine templates if available
			if (! empty($partial))
				$this->template->partial($partial);

			$this->template->page = $page;

		}

	}


}