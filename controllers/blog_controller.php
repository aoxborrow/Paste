<?php

class blog_controller extends template_controller {

	public function page() {

		$this->template->content = '<h1>Blog Controller</h1>';

	}

	public function archive() {

		// mustache not really needed for these static pages
		$this->template->content = '<h1>Archive</h1>';

	}


}