<?php

// content library
class Content {

	// content database
	protected static $db;

	// content file extension
	public static $ext = '.html';

	// return cached content database or load all content
	public static function db() {

		if (self::$db === NULL) {

			// check cache for content database
			self::$db = Cache::instance()->get('__db__');

			if (empty(self::$db)) {

				// traverse content directory and load all content
				self::$db = self::load_section(CONTENTPATH);

				// store content database in cache
				Cache::instance()->set('__db__', self::$db);
			}

		}

		return self::$db;

	}

		/*
		public static function build_file_cache($dir = '.') {
	    # build file cache
	    $files = glob($dir.'/*');
	    $files = is_array($files) ? $files : array();
	    foreach($files as $path) {
	      $file = basename($path);
	      if(substr($file, 0, 1) == "." || $file == "_cache") continue;
	      if(is_dir($path)) self::build_file_cache($path);
	      if(is_readable($path)) {
	        self::$file_cache[$dir][] = array(
	          'path' => $path,
	          'file_name' => $file,
	          'is_folder' => (is_dir($path) ? 1 : 0),
	          'mtime' => filemtime($path)
	        );
	      }
	    }
	  }
	*/

	// retrieve single page by properties
	public static function find($terms) {

		$pages = self::find_all($terms);

		return (empty($pages)) ? FALSE : current($pages);

	}

	// filter and return pages by properties
	public static function find_all($terms) {

		$pages = array();

		foreach (self::db() as $page) {

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


	public static function validate_cache() {

		// TODO: a hash could be created from the current page db, by loading the filemtime as a property for each page, and creating a hash of the flattened DB
		// then the live hash could be calculated by quickly iterating the content dir and creating a hash of filenames.mtimes
		$mtime = Cache::instance()->get_mtime('__db__');
		$expires = Cache::instance()->get_expiration('__db__');

		// iterate through content dirs, find latest mtime
		$times = self::get_mtime(CONTENTPATH);
		rsort($times);
		$latest = current($times);

		echo '<br/>';
		echo "Mtime: ".date("F j, Y, g:i a", $mtime)."\n";
		echo "Expires: ".date("F j, Y, g:i a", $expires)."\n";
		echo "Most Recent Content: ".date("F j, Y, g:i a", $latest)."\n";

		echo "<br/>Automated cache clear: ".(($latest > $mtime) ? 'YES' : ' NO');

	}

	// recursively get content modified times
	public static function get_mtime($path) {

		$path = rtrim($path, '/');

		$times = array();

		foreach (self::list_dir($path) as $file => $name) {

			// check if it's a sub directory
			if (is_dir($path.'/'.$file)) {

				$times[] = filemtime($path.'/'.$file);
				$times = array_merge($times, self::get_mtime($path.'/'.$file));
				echo "dir: ".$file." => ".date("F j, Y, g:i a", filemtime($path.'/'.$file))."\n";

			} else {

				$times[] = filemtime($path.'/'.$file);
				echo "file: ".$file." => ".date("F j, Y, g:i a", filemtime($path.'/'.$file))."\n";


			}
		}

		return $times;

	}

}