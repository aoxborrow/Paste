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
		// $this->template->content = '<p><b>'.(($this->template->current_section !== NULL) ? $this->template->current_section.' / ' : '').$this->template->current_page.'</b></p>';

		// TODO: cache rendered pages with their mustache template
		// get requested page from content database

		$cache_key = $this->current_section.'_'.$this->current_page;

		$content = Cache::instance()->get($cache_key);

		if (empty($content)) {

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

				// set page title similar to breadcrumbs
				// $this->template->title = $page->title.' - '.$this->template->title;

				// get site template
				$site_template = file_get_contents(realpath(TEMPLATEPATH.$this->site_template));

				if (! empty($page->template)) {

					// ensure .mustache file extension
					$page_template = (strstr($page->template, '.mustache')) ? $page->template : $page->template.'.mustache';

					// get page template
					$page_template = file_get_contents(realpath(TEMPLATEPATH.$page_template));

					// just replace {{{content}}} with page_template instead of using partials
					$site_template = str_replace('{{{content}}}', $page_template, $site_template);

					// passing template and view model to Mustache during runtime, so that we don't store Mustache properties in cache
					//$this->template = new Mustache($site_template, $page, array('page' => $page_template));

				}

				//$this->template = new Mustache($site_template, $page);
				$content = new Mustache($site_template, $page);
				$content = $content->render();
				Cache::instance()->set($cache_key, $content);

			}

		}

		echo $content;

	}

	public function _render() {}



}