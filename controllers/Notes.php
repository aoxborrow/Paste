<?php

class notes_controller extends template_controller {

	public function index() {

		// mustache not really needed for these static pages
		$this->template->content = file_get_contents(APPPATH.'views/pages/notes.html');

	}

}