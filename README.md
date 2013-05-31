## Paste

- Paste is a super simple "CMS" that uses static files and folders instead of a database. 
- Paste aims to be light, fast and easy to maintain. 
- Paste indexes the `/content/` folder and builds a site tree and URL heirarchy. 
- Each HTML file represents a page in the menu, each folder represents a section, to infinite depth.
- [Mustache](http://mustache.github.io/) is used for logic-less templating including partials.
- Configuration is defined within the HTML source, like so:  
```html
<!-- 
@template: master
@partial: project
@title: The Page Title
@visible: TRUE // visible in menu
@label: Menu Label (optional)
-->
```
- Configuration and templating cascade down through the heirarchy.  
![Content Example](https://raw.github.com/paste/paste-example/master/assets/images/content-example.png)   


#### Design Goals

- use latest PHP tech like Composer
- simple routing with user-definable routes and closures
- [Mustache](http://mustache.github.io/) for ultra dumb templating
- flexible templates and cascading page partials
- powerful configuration via simple syntax
- takes some cues from [Stacey App](http://www.staceyapp.com/). 


#### Requirements

- PHP 5.3+
- Apache mod_rewrite
- [https://github.com/bobthecow/mustache.php](Mustache.php) (installed automatically by Composer)

## Quick Start

Just clone the [example site repo](https://github.com/paste/paste-example) and modify the content and styling to taste!


## Installation

[Use Composer](http://getcomposer.org/). Add `paste/paste` to your project's `composer.json`:
```json
{
    "require": {
        "paste/paste": "dev-master"
    }
}
```

**Create an `index.php` file to act as the front router:**
[(or copy from the example site)](https://github.com/paste/paste-example/blob/master/index.php)

```php
<?php

// composer autoload
require 'vendor/autoload.php';
use Paste\Paste;

// (optional) user defined routing
// 'route regex' => any valid callback
// matched tokens from the regex will be passed as parameters
// e.g. 'blog/post/([A-Za-z0-9]+)' => 'Class::method',
Paste::route('blog/post/([A-Za-z0-9-_]+)', function($slug) { 
	echo "Example callback route, slug: <b>$slug</b><br/>";
});

// init routing and run
Paste::run();
```
**Create an `.htaccess` file to enable URL rewriting:**
[(or copy from the example site)](https://github.com/paste/paste-example/blob/master/.htaccess) 

```apache
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

**Create the `content`, `templates`, `cache` directories in your web root. The `cache` folder should be writeable by Apache.** Your web root should end up looking something like this:
```
/cache/
/content/
	index.html
/templates/
	template.stache
/vendor/
index.php
composer.json
.htaccess
```

**Add the first content file, `content/index.html`:**

```html
<!-- 
@title: Hello World
@template: template
-->
<h3>Hello, world!</h3>
```

**Add the first template, `templates/template.stache`:**

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

**Now visit your Paste project in a web browser and take in the magic!**



## Content TK
**Structure**  
**Variables**  

## Templates TK
**Page Templates**  
**Page Partials**  
**Mustache Partials**  



### REWRITE 2013 TODOs:



- allow a rewrite base that is not root, i.e. allow running Paste from a subdirectory
- ~~make example site more generic, add dummy text and illustrative CSS for menu heirarchy~~
- ~~write new description and a quick usage guide w/ screenshots~~
- ~~single template(), rest is partials()~~
- ~~move static content stuff into Paste~~
- ~~refactor Page lib~~
	- ~~Page Constructor -- change factory to Page::from_path()~~
	- ~~update Page->is_current() and Page->is\_parent~~
	- ~~consolidate nav stuff to next() and prev(), remove unused~~
	- ~~change page->parents to something like parent_paths~~
	- ~~simplify find and find_all, etc.~~
	- ~~render menu() separately to remove invisible pages~~
	- ~~remove section in favor of parent~~
- ~~use Mustache filesystem loader for partials and cache~~
- ~~make Menu mustache partial resursive for infinite depth -- fix CSS~~
- ~~more unique syntax for page/section vars~~
- ~~proper cascading templates~~
- ~~redo the section control as suggested in Page->factory? no~~
- ~~return Paste to all static class~~
- ~~Combine Content into Page~~
- ~~Combine Controller & Content~~
- ~~Combine Page & Template classes~~
- ~~change mustache templates to just .stache~~
- ~~just link to tumblr instead of using Tumblr blog driver~~
- ~~consider only loading structure on demand (e.g. menu()), and then only loading page vars~~
- ~~does a file based site really need a file cache? -- use memcache if anything. benchmark cache vs. no cache~~
- ~~use namespacing, make autoloader PSR-0 compatible and package it for composer~~
- ~~Paste::route() syntax for user defined routes~~
- ~~separate core and a sample site for this repo, move personal portfolio stuff to separate repo~~
- ~~simplify as much as possible. too much code for what it's supposed to be~~

