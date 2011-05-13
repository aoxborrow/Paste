<?php

// simple data storage abstraction
class Storage {

	// data file extension
	public static $ext = '.html';

	// path to data files
	public static $storage_path = 'content/';


	// load individual content
	public static function load($name) {

		return file_get_contents(APPPATH.self::$storage_path.'/'.$name.self::$ext);

	}

	// load section of data files
	public static function load_section($section) {

		$section_path = APPPATH.self::$storage_path.$section.'/';

		$pages = array();

		foreach (self::list_dir($section_path) as $file => $name) {

			if (FALSE !== ($html = file_get_contents($section_path.$file.self::$ext))) {

				$dom = DOMDocument::loadHTML($html);

				$title = $dom->getElementsByTagName('h1')->item(0)->nodeValue;

				$pages[$name] = $title;

			}

		}

		return $pages;

	}

	// list directories from content path
	public static function list_sections() {

		$path = APPPATH.self::$storage_path;

		$dirs = array();

		if (is_dir($path) AND $handle = opendir($path)) {

			while (FALSE !== ($file = readdir($handle))) {
				// ignore dot dirs and files prefixed with an underscore
				if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

					if (is_dir($path.$file)) {

						$dirs[] = $file;

					}
				}
			}
		}

		return $dirs;

	}


	// return sorted file list
	public static function list_dir($path) {

		$files = array();

		if (is_dir($path) AND $handle = opendir($path)) {

			$files['numeric'] = array();
			$files['alpha'] = array();

			while (FALSE !== ($file = readdir($handle))) {
				// ignore dot dirs and files prefixed with an underscore
				if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

					// file name without extension
					$file = basename($file, self::$ext);

					// split filename by initial period, limited to two parts
					$parts = explode('.', $file, 2);

					// name is chars after intial period if one exists
					$name = (count($parts) > 1) ? $parts[1] : $parts[0];

					// keep numeric and alpha files separate for sorting
					$type = (is_numeric($file[0])) ? 'numeric' : 'alpha';

					// use filename as sort key
					$files[$type][$file] = $name;

				}
			}

			closedir($handle);

			// sort files via natural text comparison, like OSX Finder
			uksort($files['alpha'], 'strnatcasecmp');
			uksort($files['numeric'], 'strnatcasecmp');

			// flip numeric keys to descending order, put alpha files last
			$files = array_reverse($files['numeric']) + $files['alpha'];

		}

		// return sorted array (filenames => basenames)
		return $files;

	}

}