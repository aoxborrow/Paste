<?php

class index_controller extends template_controller {

	public function index() {

		$this->template->content = Content::load('index')->content;

	}

}