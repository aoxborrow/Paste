<?php

class about_controller extends template_controller {

	public function index() {

		$this->template->content = Storage::load('about');

	}

}