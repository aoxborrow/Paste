<?php

namespace Paste;

// content library
class Content {

	// content "database"
	public static $db;

	// content file extension
	public static $ext = '.html';

	// load content database
	public static function load() {

		// traverse content directory and load all content
		if (empty(self::$db))
			self::$db = self::load_section(Paste::$content_path);
		
	}

	// retrieve single page by properties
	public static function find($terms) {
		
		$pages = self::find_all($terms);

		return (empty($pages)) ? FALSE : current($pages);

	}

	// filter and return pages by properties
	public static function find_all($terms) {
		
		// ensure we have content loaded
		self::load();

		$pages = array();

		foreach (self::$db as $page) {

			foreach ($terms as $property => $value) {

				if ($page->$property !== $value)
					// skip to next page if property doesn't match
					continue 2;

			}

			// clone the page object so we don't alter original
			$pages[] = clone $page;

		}

		return $pages;

	}

	// returns page names in a flat array
	public static function find_names($terms) {

		$pages = array();

		foreach (self::find_all($terms) as $page) {

			$pages[] = $page->name;

		}

		return $pages;

	}

	// get section child pages
	public static function section($section) {

		return self::find_all(array('section' => $section));

	}

	// recursively load sections of content
	public static function load_section($path) {

		$pages = array();

		foreach (self::list_path($path) as $file) {
			
			// sub directory
			if (is_dir($path.$file))
				$pages = array_merge($pages, self::load_section($path.$file.'/'));

			// content file with proper extension
			if (is_file($path.$file) AND strpos($file, self::$ext))
				$pages[] = Page::factory($path.$file);

		}
		
		return $pages;

	}

	// return directory list
	public static function list_path($path) {

		$files = array();

		if (($handle = opendir($path)) === FALSE)
			return $files;

		while (($file = readdir($handle)) !== FALSE) {

			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {

				$files[] = $file;

			}
		}

		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		usort($files, 'strnatcasecmp');

		// return sorted array (filenames => basenames)
		return $files;

	}

	// get base filename without sorting prefix or extension
	public static function base_name($file) {

		// get file name without content extension
		$name = basename($file, self::$ext);

		// base name is everything after intial period if one exists
		$prefix = strpos($name, '.');

		// strip prefix and return cleaned name
		return ($prefix) ? substr($name, $prefix + 1) : $name;

	}

}