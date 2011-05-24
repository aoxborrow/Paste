<?php

// debug controller for testing
class debug_controller extends template_controller {

	public function __construct() {

		parent::__construct();

		echo '<pre>';

	}

	public function index() {

		var_dump(array_values(Pastefolio::$pages));
		//var_dump($this->template->menu->pages());

		//var_dump(Page::factory('contact')->to_array());

	}

	public function structure() {


		foreach (Content::$pages as $page) {

			//if ($page->section === NULL) {

				if ($page->is_section) echo '<b>';
				if ($page->section === '_root') echo '&nbsp;&nbsp;&nbsp;';
				if ($page->section !== NULL and $page->section !== '_root') echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				echo $page->section.' / ';
				echo $page->name.' : '.$page->title.' : <i>'.$page->path.'</i><br/>';
				if ($page->is_section) echo '</b>';


			//}
		}


		echo '<br/><br/>';

		$pages = Content::$pages;

		foreach ($pages as $page) {
			echo $page->name.'<br/>';
		}




		//print_r(Content::load_section('projects'));
		//print_r(Content::list_dir('/', TRUE));
		//print_r(Router::$routes);
		//print_r(Content::list_sections());


	}

	public function _render() {

		echo '</pre>';

	}

}