## Paste (2013)

**REWRITE TODOs:**

- Combine Page & Template classes (+ Content?)
- use newer composer pkg of Mustache. use filesystem loader for partials and cache
- redo the section control as suggested in Page.php L75?
- render menu() separately to remove invisible pages
- update Page->current() and add Page->section()
- more unique syntax for page vars
- make a Pre lib!
- X return Paste to all static class
- X Combine Controller & Content
- X change mustache templates to just .stache
- X just link to tumblr instead of using Tumblr blog driver
- X consider only loading structure on demand (e.g. menu()), and then only loading page vars
- X does a file based site really need a file cache? -- use memcache if anything. benchmark cache vs. no cache
- X use namespacing, make autoloader PSR-0 compatible and package it for composer
- X Paste::route() syntax for user defined routes
- X separate core and a sample site for this repo, move personal portfolio stuff to separate repo
- X simplify as much as possible. too much code for what it's supposed to be



### Pastefolio (2011)

- Pastefolio is a simple portfolio "CMS" that uses static HTML files instead of a database.
- It aims to be super fast and easy to maintain. It takes some cues from [http://www.staceyapp.com/](Stacey App).
- Pastefolio takes the structure of the /content/ folder and builds the menu and heirarchy. 
- Each html file represents a page in the menu, each folder represents a section, to infinite depth.
- Variables can be set within the HTML source, like so: <!-- template: master -->. Variables cascade through the child sections.
- Mustache.php is used for templating including partials.
- It uses OOP and the MVC pattern and requires PHP5.

### Design Goals

* experiment with various new techs
* barebones micro MVC pattern
* simple routing with user-defined routes
* autoloader
* super thin controllers
* robust models
* mustache for ultra dumb templates
* abstract a separate "pastefolio" core system, create demo app with basic template