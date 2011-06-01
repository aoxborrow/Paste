<?php

// location of this file, index.php
define('DOCROOT', dirname(__FILE__).'/');

// directory where content files are stored
$content_path = DOCROOT.'content';

// directory where mustache templates are stored
$template_path = DOCROOT.'templates';

// directory for cache (must be writeable)
$cache_path = DOCROOT.'cache';

// define routing rules, longest first
// generally uses Kohana routing conventions: http://docs.kohanaphp.com/general/routing
$routes = array(
	'debug' => 'debug', // temporary
	'notes' => 'blog', // default blog page
	'notes/archive' => 'blog/archive', // blog archive
	'notes/page/([A-Za-z0-9]+)' => 'blog/page/$1', // blog pages
	'notes/([A-Za-z0-9]+)' => 'blog/post/$1', // blog post
	'_default' => 'content', // default content controller
);

// Pastefolio bootstrap
require_once 'pastefolio/bootstrap.php';
