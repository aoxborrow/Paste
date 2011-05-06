<?php

// menu view model
class Menu extends Mustache {

	// define main menu
	private static $_main_menu = array(
		'index' => 'Home',
		'notes' => 'Lab Notes',
		'info' => 'Information',
	);

	// define project menu
	private static $_project_menu = array(

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

	// define mustache template
	protected $_template = 'menu.mustache';

	// builds main menu array for mustache
	public function main_menu() {

		$menu = array();

		// convert menu definition into key values for mustache
		foreach (self::$_main_menu as $name => $title) {

			$menu[] = array(
				'name' => $name,
				'title' => $title,
				'current' => ($name == $this->current_page),
			);
		}

		return $menu;

	}

	// builds project menu array for mustache
	public function project_menu() {

		$menu = array();

		// convert menu definition into key values for mustache
		foreach (self::$_project_menu as $category => $pages) {

			$category = array(
				'category' => $category,
				'pages' => array(),
			);

			foreach ($pages as $name => $title) {

				$category['pages'][] = array(
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
		
		return array_shift(array_keys(self::$_project_menu['Applications']));
		
	}

	// returns project menu array without categories
	public function flat_menu() {

		$flat_menu = array();

		// convert menu definition to key values, ignoring categories
		foreach (self::$_project_menu as $category => $cat_pages) {

			foreach ($cat_pages as $name => $title) {

				$flat_menu[] = array(
					'name' => $name,
					'title' => $title,
					'current' => ($name == $this->current_page),
				);
			}
		}

		return $flat_menu;

	}

	public function render() {

		return parent::render(file_get_contents(APPPATH.'views/'.$this->_template));

	}

}