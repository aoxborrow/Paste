<?php
// Pastefolio index.php

// define system paths, can be absolute or relative to this file
// directory where Pastefolio is located
$app_path = 'pastefolio';

// directory where content files are stored
$content_path = 'content';

// directory where mustache templates are stored
$template_path = 'templates';

// directory for cache (must be writeable)
$cache_path = 'cache';


// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog', // default blog page
	'notes/archive' => 'blog/archive', // blog archive
	'notes/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'_default' => 'content', // default content controller
);

// Pastefolio bootstrap
require_once 'pastefolio/bootstrap.php';
