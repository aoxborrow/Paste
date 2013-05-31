### Paste

- Paste is a super simple "CMS" that uses static files and folders instead of a database. 
- Paste aims to be light, fast and easy to maintain. 
- Paste takes some cues from [Stacey App](http://www.staceyapp.com/). 
- Paste indexes the /content/ folder and builds the menu and URL heirarchy. 
- [Mustache](http://mustache.github.io/) is used for logic-less templating including partials.
- Each HTML file represents a page in the menu (if visible), each folder represents a section, to infinite depth.
- Configuration is defined within the HTML source, like so: 
	`<!-- @template: master // the parent template -->` 
	`<!-- @partial: nav // partial template -->` 
	`<!-- @title: The Page Title // HTML Page Title-->` 
	`<!-- @visible: TRUE // visible in menu -->` 
	`<!-- @label: Menu Label // page name in menu -->` 
- Configuration and templating cascade down through the heirarchy.

![Content Example](https://raw.github.com/paste/paste-example/master/assets/images/finder-view.png)  

Design Goals
------------

* use latest PHP technologies like Composer
* simple routing with user-definable routes and closures
* mustache for ultra dumb templates and partials
* cascading, flexible templates and configuration, easy syntax
* fast, light, easy to maintain

Requirements
------------

- PHP 5.3+
- Apache mod_rewrite
- [https://github.com/bobthecow/mustache.php](Mustache.php) (installed automatically by Composer)

Quick Start
-----------
Clone the [example site repo](https://github.com/paste/paste-example) and modify the content and styling to taste!


Installation
------------

##### Step 1
[Use Composer](http://getcomposer.org/). Add `paste/paste` to your project's `composer.json`:
```json
{
    "require": {
        "paste/paste": "dev-master"
    }
}
```

##### Step 2
Create an `index.php` file to act as the front router:
[(Copy from the example site)](https://github.com/paste/paste-example/blob/master/index.php)

```php
<?php

// composer autoload
require 'vendor/autoload.php';
use Paste\Paste;

// optional, user defined routing
// 'route regex' => any valid callback
// matched tokens from the regex will be passed as parameters
// e.g. 'blog/post/([A-Za-z0-9]+)' => 'Class::method',
Paste::route('blog/post/([A-Za-z0-9-_]+)', function($slug) { 
	echo "Example callback route, slug: <b>$slug</b><br/>";
});

// init routing and run
Paste::run();
```
##### Step 3
Create an `.htaccess` file to enable URL rewriting:
[Copy from the example site](https://github.com/paste/paste-example/blob/master/.htaccess) 

```htaccess
# don't list directories
Options -Indexes

# Turn on URL rewriting
RewriteEngine On

# Installation directory
RewriteBase /

# Protect dot files from being viewed
<Files .*>
	Order Deny,Allow
	Deny From All
</Files>

# Protect application and system files from being viewed
RewriteRule ^(?:vendor|content|templates|cache)\b.* index.php/$0 [L]

# Allow any files or directories that exist to be displayed directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewrite all other URLs to index.php/URL
RewriteRule .* index.php/$0 [PT]

# use utf-8 encoding for anything served text/plain or text/html
AddDefaultCharset utf-8

# force utf-8 for a number of file formats
AddCharset utf-8 .html .css .js .xml .json .rss
```
##### Step 4
Create the `content`, `templates`, `cache` directories in your web root. The `cache` folder should be writeable by Apache.

Add the first content file, `content/index.html`:

```html
<!-- 
@title: Hello World
@template: template
-->

<h3>Hello, world!</h3>
```

Add the master template, `templates/template.stache`:

```html
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{title}}</title>
</head>
<body>
	{{{content}}}
</body>
</html>
```

##### Step 5
Profit! Visit your Paste project in a web browser and take in the magic. Now build your content and templates out!


### Content

Structure
---------
TK

Variables
---------
TK

### Templates

Page Templates
--------------
TK

Page Partials
-------------
TK

Mustache Partials
-----------------
TK



### REWRITE 2013 TODOs:

<del>


- allow a rewrite base that is not root, i.e. allow running Paste from a directory
- X make example site more generic, add dummy text and illustrative CSS for menu heirarchy
- X write new description and a quick usage guide w/ screenshots
- X single template(), rest is partials()
- X move static content stuff into Paste
- X refactor Page lib
	- X Page Constructor -- change factory to Page::from_path()
	- X update Page->is_current() and Page->is\_parent
	- X consolidate nav stuff to next() and prev(), remove unused
	- X change page->parents to something like parent_paths
	- X simplify find and find_all, etc.
	- X render menu() separately to remove invisible pages
	- X remove section in favor of parent
- X use Mustache filesystem loader for partials and cache
- X make Menu mustache partial resursive for infinite depth -- fix CSS
- X more unique syntax for page/section vars
- X proper cascading templates
- X redo the section control as suggested in Page->factory? no
- X return Paste to all static class
- X Combine Content into Page
- X Combine Controller & Content
- X Combine Page & Template classes
- X change mustache templates to just .stache
- X just link to tumblr instead of using Tumblr blog driver
- X consider only loading structure on demand (e.g. menu()), and then only loading page vars
- X does a file based site really need a file cache? -- use memcache if anything. benchmark cache vs. no cache
- X use namespacing, make autoloader PSR-0 compatible and package it for composer
- X Paste::route() syntax for user defined routes
- X separate core and a sample site for this repo, move personal portfolio stuff to separate repo
- X simplify as much as possible. too much code for what it's supposed to be

</del>