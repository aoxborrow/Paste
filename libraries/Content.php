<?php

// simple content storage abstraction
class Content {

	// content file extension
	public static $ext = '.html';

	// root section name
	public static $root_section = NULL;

	// root section name
	public static $index = 'index';


	// content database
	public static $pages;


	// traverse content directory and load everything...
	// TODO: load all content data at once, so we can lookup file names easily
	// TODO: allow inifinite section depth
	// TODO: try having root_section as NULL

	public static function init() {

		self::$pages = self::load_section();

	}

	// filter content by property
	public static function filter($property, $value, $operator = '==') {

		$pages = array();

		foreach (self::$pages as $page) {

			if ($page->$property === $value) {

				$pages[] = $page;

			}
		}

		return $pages;

	}


	// recursively load section of data files
	public static function load_section($section = NULL, $path = '/', $parent = NULL) {

		$pages = array();

		foreach (self::list_dir($path) as $file => $name) {

			// check if file is page or section
			if (strstr($file, self::$ext) === FALSE) {

				$pages = array_merge($pages, self::load_section($name, $file, $section));

			} else {

				$page = new Page($name, $path.'/'.$file);

				// TODO: clean this up
				// index files are created as the section parent
				if ($name == 'index') {
					if ($section === NULL) {
						$page->name = self::$index;
					} else {
						$page->name = $section;
					}
					$page->is_section = TRUE;
					$page->section = $parent;
				} else {
					$page->section = $section;
				}
				$pages[] = $page;

			}

		}

		return $pages;

	}


	// TODO: move sorting to configurable function in Menu, we don't care about sorting here
	// return directory list
	public static function list_dir($path = '/') {

		$files = array();

		// path is relative to content path
		$path = realpath(CONTENTPATH.$path).'/';

		if (FALSE === ($handle = opendir($path)))
			return $files;

		while (FALSE !== ($file = readdir($handle))) {

			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

				// file name without extension
				$name = basename($file, self::$ext);

				// split filename by initial period, limited to two parts
				$parts = explode('.', $name, 2);

				// page name is everything after intial period if one exists
				$name = (count($parts) > 1) ? $parts[1] : $parts[0];

				// use filename as sort key
				$files[$file] = $name;

			}
		}

		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		uksort($files, 'strnatcasecmp');

		// return sorted array (filenames => basenames)
		return $files;

	}

	// TODO: move sorting to configurable function in Menu, we don't care about sorting here
	// return sorted content list
	public static function sorted_dir($path = '/') {

		// path is relative to content path
		$path = realpath(CONTENTPATH.$path).'/';

		if (FALSE === ($handle = opendir($path)))
			return array();

		$files = array(
			'numeric' => array(),
			'alpha' => array(),
		);

		while (FALSE !== ($file = readdir($handle))) {

			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

				// file name without extension
				$name = basename($file, self::$ext);

				// split filename by initial period, limited to two parts
				$parts = explode('.', $name, 2);

				// page name is everything after intial period if one exists
				$name = (count($parts) > 1) ? $parts[1] : $parts[0];

				// keep numeric and alpha files separate for sorting
				$type = (is_numeric($file[0])) ? 'numeric' : 'alpha';

				// use filename as sort key
				$files[$type][$file] = $name;

			}
		}

		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		uksort($files['alpha'], 'strnatcasecmp');
		uksort($files['numeric'], 'strnatcasecmp');

		// flip numeric keys to descending order, put alpha files last
		$files = array_reverse($files['numeric']) + $files['alpha'];

		// return sorted array (filenames => basenames)
		return $files;

	}

}