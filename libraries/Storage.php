<?php

// simple data storage abstraction
class Storage {
	
	// data file extension
	public static $ext = '.yaml';
	
	// path to data files
	public static $storage_path = 'views/projects/';
	
	
	// load individual data object
	public static function load($name) {
		
		return (object) sfYaml::load(APPPATH.self::$storage_path.$name.self::$ext);
		
	}
	
	// list directory of data files
	public static function list_dir() {
		
		$files = array();

		if ($handle = opendir(APPPATH.self::$storage_path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' AND $file != '..') {
					$name = basename($file, self::$ext);
					$files[] = $name;
				}
			}
			closedir($handle);			
		}

		return $files;
		
	}	
	
	
	
}