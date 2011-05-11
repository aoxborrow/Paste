<?php

// menu view model
class Menu extends Mustache {

	// define project menu
	private static $_project_menu = array(

		'Projects' => array(
			'ifloorplan' => 'iFloorPlan',
			'twl' => 'T.W. Lewis',
			'killington' => 'Killington',
			'kb' => 'KB Home',
			'mvr' => 'Monte Vista',
			'contact' => 'Contact Design',
			'silverplatter' => 'Silver Platter',
			'tropical' => 'Tropical Salvage',
			'rockwell' => 'Rockwell Partners',
			'viridian' => 'Viridian Group',
			'trade' => 'Design Trade',
			'modified' => 'Modified Arts',
			'blufish' => 'Blufish Design',
			'logos' => 'Logos',
		),

	);
	
	// define project menu
	private static $__project_menu = array(

		'Applications' => array(
			'ifloorplan' => 'iFloorPlan',
			'twl' => 'T.W. Lewis',
			'killington' => 'Killington',
			'kb' => 'KB Home',
			'mvr' => 'Monte Vista',
		),

		'Websites' => array(
			'contact' => 'Contact Design',
			'silverplatter' => 'Silver Platter',
			'tropical' => 'Tropical Salvage',
			'rockwell' => 'Rockwell Partners',
			'viridian' => 'Viridian Group',
			'trade' => 'Design Trade',
			'modified' => 'Modified Arts',
			'blufish' => 'Blufish Design',
		),

		'Design' => array(
			'logos' => 'Logos',
		),

	);	

	// used for selected menu item, reference to template var
	public $current_page = NULL;
	
	// pages menu
	public static $pages = array();

	// define mustache template
	protected $_template = 'templates/menu.mustache';

	// builds main menu array for mustache
	public function pages_menu() {

		$menu = array();

		// convert menu definition into key values for mustache
		foreach (self::$pages as $name => $title) {

			$menu[] = array(
				'name' => $name,
				'title' => $title,
				'current' => ($name == $this->current_page),
			);
		}

		return $menu;

	}

	// builds project menu array for mustache
	public function projects_menu() {

		$menu = array();

		// convert menu definition into key values for mustache
		foreach (self::$_project_menu as $category => $projects) {

			$category = array(
				'category' => $category,
				'projects' => array(),
			);

			foreach ($projects as $name => $title) {

				$category['projects'][] = array(
					'name' => $name,
					'title' => $title,
					'current' => ($name == $this->current_page),
				);
			}

			$menu[] = $category;
		}

		return $menu;

	}
	
	public static function first_project() {
		
		// get first item of flat project menu
		return array_shift(array_keys(self::flat_project_menu()));
		
	}
	
	public static function last_project() {
		
		// get last item of flat project menu
		return array_pop(array_keys(self::flat_project_menu()));
		
	}
	
	// returns project menu array without categories
	public static function flat_project_menu() {

		$flat_menu = array();

		// convert menu definition to key values, ignoring categories
		foreach (self::$_project_menu as $category => $cat_pages) {

			foreach ($cat_pages as $name => $title) {

				$flat_menu[$name] = $title;
				
			}
		}

		return $flat_menu;

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