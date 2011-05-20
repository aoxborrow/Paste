<?php

// menu view model
class Menu extends Mustache {

	// menu built from content structure
	public static $menu;

	// used for selected section, reference to template var
	public $current_section;

	// used for selected sub-menu item, reference to template var
	public $current_page;

	// define mustache template
	public $template = 'menu.mustache';

	// builds page menu array for mustache
	public function menu() {

		self::$menu = array(
			'section' => Content::$root_section,
			'is_section' => TRUE,
			'title' => 'Root Section',
			'current' => (Content::$root_section == $this->current_section),
			'pages' => array(),
		);

		// TODO: make this recursive for sections
		// convert content structure into key values for mustache
		foreach (Content::$pages as $page) {

			if ($page->section == Content::$root_section) {

				// root sections
				if ($page->is_section) {

					$section = array(
						'is_section' => TRUE,
						'section' => $page->name,
						'title' => $page->title,
						'current' => ($page->name == $this->current_section),
						'sub_pages' => array(),
					);

					foreach (Content::$pages as $sub_page) {

						if ($sub_page->section == $page->name) {
							$section['sub_pages'][] = array(
								'is_section' => FALSE,
								'section' => $sub_page->section,
								'name' => $sub_page->name,
								'title' => $sub_page->title,
								'current' => ($sub_page->name == $this->current_page),
							);
						}
					}

					self::$menu['pages'][] = $section;

				// root pages
				} else {

					self::$menu['pages'][] = array(
						'is_section' => FALSE,
						'section' => $page->section,
						'name' => $page->name,
						'title' => $page->title,
						'current' => ($page->name == $this->current_page),
					);

				}


			}

		}

		return self::$menu;

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

		return parent::render(file_get_contents(TEMPLATEPATH.$this->template));

	}

}