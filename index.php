<?php

// directory where content files are stored, relative to this file
$content_path = 'content';

// directory where mustache templates are stored, relative to this file
$template_path = 'templates';

// directory for cache, relative to this file (must be writeable)
$cache_path = 'cache';

// set cache lifetime in seconds. 0 or FALSE disables cache
$cache_time = 0;

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
$routes = array(
	'debug' => 'debug', // temporary
	'_default' => 'content', // default content controller
);

// location of this file, index.php
$doc_root = __DIR__.'/';

// Paste bootstrap
require_once 'Paste/bootstrap.php';
