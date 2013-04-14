## Paste

**REWRITE 2013 TODO:**

- separate core and a sample site for this repo, move personal portfolio stuff to separate repo
- use namespacing, make autoloader PSR-0 compatible and package it for composer?
- use newer composer pkg of Mustache. look into new filesystem loader for partials and cache
- does a file based site really need a file cache? -- use memcache if anything. benchmark cache vs. no cache
- ditch _controller extension.. change templates to just .html
- probably ditch tumblr... either write a file based blog driver.. Markdown? or just link to a tumblr
- redo the section control as suggested in Page.php L75
- more unique syntax for page vars
- use class const instead of global constants/config.. e.g Paste::APP_DIR
- combine content and template controller?
- consider dumping the router and controllers completely -- maybe use defined Larvel-style callback routes
- simplify as much as possible. too much code for what it's supposed to be
- make a Pre lib!



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