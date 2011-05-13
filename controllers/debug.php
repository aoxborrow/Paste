<?php

class debug_controller {


	public function index() {

		echo '<pre>';

		print_r(Content::load_section('projects'));
		//print_r(Content::list_dir('/', TRUE));
		//print_r(Router::$routes);
		//print_r(Content::list_sections());

		echo '</pre>';

	}

	public function _render() {}

}