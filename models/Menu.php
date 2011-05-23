<?php

// menu view model
class Menu extends Mustache {

	// define mustache template
	public static $template = 'menu.mustache';

	// convert content structure into key values for mustache
	public function pages() {

		$menu = array();

		// get root content pages
		foreach (Page::find_all(array('section' => NULL)) as $page) {

			if ($page->is_visible) {

				// add children if parent page is section
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

	// returns project menu array without categories
	public static function flat_project_menu() {

		return self::$menu['projects'][1];

	}

	public static function first_project() {

		// get first item of flat project menu
		return array_shift(array_keys(self::flat_project_menu()));

	}

	public static function last_project() {

		// get last item of flat project menu
		return array_pop(array_keys(self::flat_project_menu()));

	}

	// returns project name relative to specified project
	public static function relative_project($current_key, $offset = 1) {

		// create key map from flat menu
		$keys = array_keys(self::flat_project_menu());

		// find current key
		$current_key_index = array_search($current_key, $keys);

		// return desired offset, if in array
		if (isset($keys[$current_key_index + $offset])) {
			return $keys[$current_key_index + $offset];
		}

		// otherwise return false
		return FALSE;
	}


	public function render() {

		return parent::render(file_get_contents(realpath(TEMPLATEPATH.self::$template)));

	}

}