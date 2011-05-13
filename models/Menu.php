<?php

// menu view model
class Menu extends Mustache {

	// menu sections
	public static $menu = array();

	// used for selected section, reference to template var
	public $current_section;

	// used for selected sub-menu item, reference to template var
	public $current_page;

	// define mustache template
	protected $_template = 'templates/menu.mustache';


	// builds project menu array for mustache
	public function menu() {

		$menu = array();

		// convert menu definition into key values for mustache
		foreach (self::$menu as $section_name => $section_title) {

			$section = array(
				'section' => $section_name,
				'title' => $section_title,
				'current' => ($section_name == $this->current_section),
				'pages' => array(),
			);

			$pages = Storage::load_section($section_name);

			foreach ($pages as $name => $title) {

				$section['pages'][] = array(
					'name' => $name,
					'title' => $title,
					'current' => ($name == $this->current_page),
				);
			}

			$menu[] = $section;
		}

		return $menu;

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

		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));

	}

}