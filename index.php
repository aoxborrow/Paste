<?php

// directory where content files are stored, relative to this file
$content_path = 'content';

// directory where mustache templates are stored, relative to this file
$template_path = 'templates';

// directory for cache, relative to this file (must be writeable)
$cache_path = 'cache';

// set cache lifetime in seconds. 0 or FALSE disables cache
$cache_time = 600;

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog', // default blog page
	'notes/archive' => 'blog/archive', // blog archive
	'notes/page/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'notes/post/([A-Za-z0-9]+)' => 'blog/post/$1', // blog post
	'_default' => 'content', // default content controller
);

// location of this file, index.php
define('DOCROOT', dirname(__FILE__).'/');

// Pastefolio bootstrap
require_once 'pastefolio/bootstrap.php';
