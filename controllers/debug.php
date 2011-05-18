<?php

class debug_controller {


	public function index() {

		echo '<pre>';

		foreach (Content::$pages as $page) {

			//if ($page->parent === NULL) {

				if ($page->is_parent) echo '<b>';
				if ($page->parent === '_root') echo '&nbsp;&nbsp;&nbsp;';
				if ($page->parent !== NULL and $page->parent !== '_root') echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				echo $page->parent.' / ';
				echo $page->name.' : '.$page->title.' : <i>'.$page->path.'</i><br/>';
				if ($page->is_parent) echo '</b>';


			//}
		}


		echo '<br/><br/>';

		$pages = Content::$pages;

		foreach ($pages as $page) {
			if ($page->parent == '_root') {
				echo $page->name.'<br/>';
			}
		}




		//print_r(Content::load_section('projects'));
		//print_r(Content::list_dir('/', TRUE));
		//print_r(Router::$routes);
		//print_r(Content::list_sections());

		echo '</pre>';

	}

	public function _render() {}

}