<?php

// simple content storage abstraction
class Content {

	// content file extension
	public static $ext = '.html';

	// root section name
	public static $index = 'index';

	// content database
	public static $pages;


	// traverse content directory and load everything...
	public static function init() {

		self::$pages = self::load_section();

	}

	// filter and return content by property
	public static function filter($property, $value, $equals = TRUE) {

		$pages = array();

		foreach (self::$pages as $page) {

			if ($page->$property === $value) {

				$pages[] = $page;

			}
		}

		return $pages;

	}

	// retrieve page in content database
	public static function get($name) {

		foreach (self::$pages as $page) {
			if ($page->name == $name)
				return $page;
		}

		return FALSE;

	}


	// TODO: allow inifinite section depth
	// TODO: caching $pages data
	// recursively load section of data files
	public static function load_section($section = NULL, $path = '/', $parent = NULL) {

		$pages = array();

		foreach (self::list_dir($path) as $file => $name) {

			// check if file is page or section
			if (strstr($file, self::$ext) === FALSE) {

				$pages = array_merge($pages, self::load_section($name, $file, $section));

			} else {

				$page = new Page($name, $path.'/'.$file, $section, $parent);

				$pages[] = $page;

			}
		}

		return $pages;

	}


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

	// TODO: move sorting to configurable functin in Menu, we don't care about sorting here
	// return sorted content list
	public static function old_sorted_list_dir($path = '/') {

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