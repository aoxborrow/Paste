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
	protected $_template = 'templates/menu.mustache';

	// builds project menu array for mustache
	public function menu() {
		
		if (self::$menu === NULL) {

			self::$menu = array();

			// convert content structure into key values for mustache
			foreach (Content::sections() as $section) {

				$pages = Content::load_section($section);
				
				$section = array(
					'section' => $section,
					'title' => $pages['index']->title,
					'current' => ($section == $this->current_section),
					'pages' => array(),
				);
				
				unset($pages['index']);
				
				/*
				$section = array(
					'section' => $section_name,
					'title' => $section_title,
					'current' => ($section_name == $this->current_section),
					'pages' => array(),
				);*/

				foreach ($pages as $name => $page) {

					$section['pages'][] = array(
						'name' => $name,
						'title' => $page->title,
						'current' => ($name == $this->current_page),
					);
				}

				self::$menu[] = $section;
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

		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));

	}

}