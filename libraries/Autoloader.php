<?php

function autoloader($class) {

	// return if class already exists
	if (class_exists($class, FALSE)) {
		return TRUE;
	}

	// convert the last word of the class name to a folder, i.e. "template_controller" => "controllers" 
	$index	= strrpos($class, '_');
	$folder = strtolower(($index == 0 ? $class : substr($class, $index + 1))).'s';
	$file = substr($class, 0, $index).'.php';
	
	// find the file and load it, i.e. controllers/template_controller.php
	if (file_exists(APPPATH.$folder.'/'.$file)) {	
		
		require_once(APPPATH.$folder.'/'.$file);
		return TRUE;
	
	// try the libraries folder
	} elseif (file_exists(APPPATH.'libraries/'.$class.'.php')) {
		
		require_once(APPPATH.'libraries/'.$class.'.php');
		return TRUE;
	
	// try the models folder			
	} elseif (file_exists(APPPATH.'models/'.$class.'.php')) {
		
		require_once(APPPATH.'models/'.$class.'.php');
		return TRUE;
	
	}
	
	// couldn't find the file
	return FALSE;
}

spl_autoload_register('autoloader');