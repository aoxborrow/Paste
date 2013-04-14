<?php

// directory where content files are stored, relative to this file
$content_path = 'content';

// directory where mustache templates are stored, relative to this file
$template_path = 'templates';

// directory for cache, relative to this file (must be writeable)
$cache_path = 'cache';

// set cache lifetime in seconds. 0 or FALSE disables cache
$cache_time = 0;

// define routing callbacks
// 'route regex' => any valid callback
// matched tokens from the regex will be passed as parameters
// e.g. 'blog/post/([A-Za-z0-9]+)' => 'Class::method',
$routes = array(
	// example user defined blog route
	'blog/post/([A-Za-z0-9-_]+)' => function($slug) { 
		echo "Example callback route, slug: <b>$slug</b><br/>";
	});

// location of this file, index.php
$doc_root = __DIR__.'/';

// Paste bootstrap
require_once 'Paste/bootstrap.php';
