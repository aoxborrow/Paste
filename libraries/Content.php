<?php

// simple content storage abstraction
class Content {

	// content file extension
	public static $ext = '.html';

	// content sections
	public static $sections;

	// content database
	public static $pages;




	// list section directories from content path
	public static function sections() {

		if (self::$sections === NULL) {

			// get content folder list
			self::$sections = array_values(self::list_dir('/', TRUE));

		}

		return self::$sections;

	}

	// load individual content page
	public static function load($name, $path) {

		$page = new StdClass;
		$page->name = $name;
		$page->path = $path;
		$page->parent = NULL;
		$page->is_parent = FALSE; // parent sections
		$page->visible = TRUE; // menu visibility
		$page->template = NULL; // allow setting this in variable
		$page->title = NULL; // get this from variable, h1 content, page name
		$page->content = NULL; // if no content, then redirect to first child
		$page->redirect = NULL; // redirect variable which is read into menu links
		// $page->thumb // future thumbnail use

		// strip any extra slashes
		// $content_path = realpath(CONTENTPATH.$path);
		$content_path = realpath($path);


		if (FALSE !== ($html = @file_get_contents($content_path))) {

			$dom = DOMDocument::loadHTML($html);

			$page->title = @$dom->getElementsByTagName('h1')->item(0)->nodeValue;
			$page->content = $html;

		} else {

			return FALSE;
			// $page->content = 'FAILED TO LOAD CONTENT YO<br/>';
			// $page->content .= 'section: '.$section.' , file: '.$file.' , name: '.$name;

		}

		return $page;

	}

	// load section of data files
	public static function load_section($section, $path = NULL, $parent = NULL) {

		$pages = array();

		$path = realpath($path).'/';

		foreach (self::list_dir($path) as $file => $name) {

			// check if file is page or section
			if (strstr($file, self::$ext) === FALSE) {

				$pages = array_merge($pages, self::load_section($name, $path.$file, $section));

			} else {

				$page = self::load($name, $path.$file);

				// index files are created as the section parent
				if ($name == 'index') {
					$page->name = $section;
					$page->is_parent = TRUE;
					$page->parent = $parent;
				} else {
					$page->parent = $section;
				}
				$pages[] = $page;

			}

		}

		return $pages;

	}

	// traverse content directory and load everything...
	// TODO: allow sorting prefix on sections
	// TODO: load all content data at once, so we can lookup file names easily
	// TODO: add visible toggle for menu visibility
	// TODO: allow inifinite section depth

	public static function init() {

		self::$pages = self::load_section('_root', CONTENTPATH);

	}



	// return sorted content list
	public static function list_dir($path = '/') {

		// path is relative to content path
		// $path = realpath(CONTENTPATH.$path).'/';
		$path = realpath($path).'/';

		if (FALSE === ($handle = opendir($path)))
			return array();

		$files = array(
			'numeric' => array(),
			'alpha' => array(),
		);

		while (FALSE !== ($file = readdir($handle))) {

			// ignore dot dirs, paths prefixed with an underscore or period
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