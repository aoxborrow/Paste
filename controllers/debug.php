<?php

class debug_controller {


	public function index() {

		echo '<pre>';

		//print_r(Storage::load_section('projects');
		//print_r(Storage::list_dir(APPPATH.'content'));

		print_r(Storage::list_sections());

		echo '</pre>';

	}

	public function _render() {}

}