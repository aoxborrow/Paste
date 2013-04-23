<?php

namespace Paste;

// content library
class Content {

	// content database
	public static $db;

	// content file extension
	public static $ext = '.html';

	// init cached content or load content database
	public static function init() {
		
		// check cache for content database
		// self::$db = Cache::instance()->get('__cache__');

		// validate content directory against cached version
		// self::validate_cache();

		if (empty(self::$db)) {

			// traverse content directory and load all content
			self::$db = self::load_section(Paste::$CONTENT_PATH);
			
			// store content database in cache
			// Cache::instance()->set('__cache__', self::$db);

		}
	}

	// retrieve single page by properties
	public static function find($terms) {

		$pages = self::find_all($terms);

		return (empty($pages)) ? FALSE : current($pages);

	}

	// filter and return pages by properties
	public static function find_all($terms) {

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


			echo $path.$file.'<br/>';

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

	// TODO: cache the results of this method so it only checks once per hour or so
	// validate cache against content directory, delete if content is newer
	public static function validate_cache() {

		// no cached db to validate
		if (empty(self::$db))
			return;

		// create hash from cached content
		$db_hash = self::db_hash();

		// create hash from list of current content files
		$content_hash = self::content_hash(CONTENT_PATH);

		// compare db hash to current content directory
		if ($db_hash !== $content_hash) {

			// clear cached db
			self::$db = NULL;

			// delete all cache files
			Cache::instance()->delete_all();

		}

	}

	// generate hash from content database
	public static function db_hash($files = '') {

		foreach (self::$db as $file) {
			$files .= $file->path.'.'.$file->mtime.',';
		}

		return md5(substr($files, 0, -1));

	}

	// generate hash from content directory files and their mtimes
	public static function content_hash($path, $hash = TRUE, $files = '') {

		foreach (self::list_path($path) as $file) {

			// sub directory
			if (is_dir($path.$file))
				$files .= self::content_hash($path.$file.'/', FALSE);

			// content file with proper extension
			if (is_file($path.$file) AND strpos($file, self::$ext))
				$files .= $path.$file.'.'.filemtime($path.$file).',';
		}

		// hash list of files if not recursive
		return ($hash) ? md5(substr($files, 0, -1)) : $files;

	}

}