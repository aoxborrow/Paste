<?php

namespace Paste;

// content library
class Content {

	// content "database"
	public static $db;

	// content file extension
	public static $ext = '.html';
	
	// content directory relative to app path
	public static $dir = 'content';
	
	// current page data model
	public static $page;

	// decipher request and render content page
	public static function render($uri = NULL) {
		
		// trim slashes
		$uri = trim($uri, '/');
		
		// decipher content request
		$request = empty($uri) ? array('index') : explode('/', $uri);

		// current section is 2nd to last argument (ie. parent2/parent1/section/page) or NULL if root section
		$section = (count($request) < 2) ? NULL : $request[count($request) - 2];

		// current page is always last argument of request
		$page = end($request);
		
		// get requested page from content database
		self::$page = self::find(array('section' => $section, 'name' => $page));
		
		// setup template instance
		$template = new Template;

		// no page found
		if (self::$page === FALSE) {

			// send 404 header
			header('HTTP/1.1 404 File Not Found');

			// draw 404 content if available
			self::$page = self::find(array('name' => '404'));
			
			// if no 404 content available, do somethin' sensible
			if (self::$page === FALSE) {

				// simple 404 page
				self::$page = new Page;
				self::$page->title = 'Error 404 - File Not Found';
				self::$page->content = '<h1>Error 404 - File Not Found</h1>';
				
			}

		// page redirect configured
		} elseif (! empty(self::$page->redirect)) {

			// redirect to url
			return Paste::redirect(self::$page->url());

		}
		
		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');
		
		// page wasn't loaded
		if (! self::$page->loaded)
			die(print_r(self::$page));
		
		// render the template 
		echo $template->render(self::$page);
		
	}
	

	// load content database
	public static function load() {
		
		// traverse content directory and load all content
		if (empty(self::$db)) {
			
			// directory where content files are stored
			$content_path = Paste::$path.Content::$dir.'/';
			
			// load root and all child sections
			self::$db = self::load_section($content_path);
			
		}
		
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