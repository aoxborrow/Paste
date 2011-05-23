<?php

function autoloader($class) {

	// return if class already exists
	if (class_exists($class, FALSE)) {
		return TRUE;
	}

	// try the controllers folder
	if (file_exists(APPPATH.'controllers/'.$class.'.php')) {

		require_once(APPPATH.'controllers/'.$class.'.php');
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