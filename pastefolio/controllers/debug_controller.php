<?php

// debug controller for testing
class debug_controller {

	public function __construct() {

		echo '<pre>';

	}

	public function index() {

		print_r(Content::load_section(CONTENTPATH));

		//Content::db();
		//Content::validate_cache();
		//var_dump(array_values(Pastefolio::$pages));

	}

	public function _render() {

		echo '</pre>';

	}

}