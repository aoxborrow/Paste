<?php

class debug_controller {


	public function index() {

		echo '<pre>';

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

		echo '</pre>';

	}

	public function _render() {}

}