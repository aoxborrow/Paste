<?php

class notes_controller extends template_controller {

	public function index() {

		$this->template->content = Page::factory('notes');

	}

	public function archive() {

		// mustache not really needed for these static pages
		$this->template->content = '<h1>Archive</h1>';

	}


}