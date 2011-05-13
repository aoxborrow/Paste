<?php

class index_controller extends template_controller {

	public function index() {

		$this->template->content = Storage::load('index');

	}

}