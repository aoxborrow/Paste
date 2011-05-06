<?php

// simple data storage abstraction
class Storage {
	
	// data file extension
	public static $ext = '.yaml';
	
	// load individual data object
	public static function load($name) {
		
		return (object) sfYaml::load(APPPATH.$name.self::$ext);
		
	}
	
	// list directory of data files
	public static function list_dir($path) {
		
		$files = array();

		if ($handle = opendir(APPPATH.$path)) {
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