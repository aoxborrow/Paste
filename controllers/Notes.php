<?php

class notes_controller extends template_controller {

	public function index() {

		$this->template->content = Storage::load('notes');

	}

	public function archive() {

		// mustache not really needed for these static pages
		$this->template->content = '<h1>Archive</h1>';

	}


}