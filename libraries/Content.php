<?php

// simple content storage abstraction
class Content {

	// content file extension
	public static $ext = '.html';
	
	// content sections
	public static $sections;


	// load individual content page
	public static function load($name, $file = NULL, $section = NULL) {
				
		
		
		$page = new StdClass;
		$page->name = $name;
		$page->section = $section;
		
		// try to assume file name if absent
		$file = (empty($file)) ? $name.self::$ext : $file.self::$ext;
		
		// strip any extra slashes 
		$content_path = realpath(CONTENTPATH.$section.'/'.$file);
		
		if (FALSE !== ($html = @file_get_contents($content_path))) {
			
			$dom = DOMDocument::loadHTML($html);

			$page->title = @$dom->getElementsByTagName('h1')->item(0)->nodeValue;
			$page->content = $html;
			
		} else {
			
			$page->content = 'FAILED TO LOAD CONTENT YO<br/>';
			$page->content .= 'section: '.$section.' , file: '.$file.' , name: '.$name;
			
		}

		return $page;

	}

	// load section of data files
	public static function load_section($section) {

		// TODO: add _root section or something equivalent to add single pages to menu
		// TODO: allow sorting prefix on sections
		// TODO: load all content data at once, so we can lookup file names easily

		$pages = array();

		foreach (self::list_dir($section) as $file => $name) {
			
			$pages[$name] = self::load($name, $file, $section);

		}

		return $pages;

	}

	// list section directories from content path
	public static function sections() {

		if (self::$sections === NULL) {
			
			// get content folder list
			self::$sections = array_values(self::list_dir('/', TRUE));
			
		}

		return self::$sections;

	}


	// return sorted content list
	public static function list_dir($path = '/', $folders_only = FALSE) {

		// path is relative to content path
		$path = realpath(CONTENTPATH.$path).'/';
		
		// can't access path
		if (FALSE === ($handle = opendir($path))) 
			return array();

		$files = array(
			'numeric' => array(),
			'alpha' => array(),
		);
		
		while (FALSE !== ($file = readdir($handle))) {
						
			// ignore dot dirs, paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.') {
				
				// only list folders when $folders_only = true
				if ($folders_only AND ! is_dir($path.$file)) 
					continue;

				// file name without extension
				$file = basename($file, self::$ext);
				
				// split filename by initial period, limited to two parts
				$parts = explode('.', $file, 2);

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