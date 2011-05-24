<?php

// menu view model
class Menu {

	// convert content structure into key values for mustache
	public function pages() {
		
		echo 'running pages...';

		$menu = array();

		// get root content pages
		foreach (Page::find_all(array('section' => NULL)) as $page) {

			if ($page->is_visible) {

				// add child pages if parent is section
				$menu[] = ($page->is_section) ? $this->recursive_pages($page) : $page;

			}
		}

		return $menu;

	}

	// recursively build menu
	private function recursive_pages($parent) {

		// add children recursively
		foreach (Page::find_all(array('section' => $parent->name)) as $page) {

			$parent->children[] = $this->recursive_pages($page);

		}

		return $parent;

	}

}