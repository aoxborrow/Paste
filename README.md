# Pastefolio

Pastefolio is a simple portfolio "CMS" that uses static HTML files instead of a database.
It aims to be super fast and easy to maintain. It takes some cues from [http://www.staceyapp.com/](Stacey App).
Pastefolio takes the structure of the /content/ folder and builds the menu and heirarchy. 
Each html file represents a page in the menu, each folder represents a section, to infinite depth.
Variables can be set within the HTML source, like so: <!-- template: master -->. Variables cascade through the child sections.
Mustache.php is used for templating including partials.
It uses OOP and the MVC pattern and requires PHP5.

## Design Goals

* experiment with various new techs
* barebones micro MVC pattern
* simple routing with user-defined routes
* auto load classes on demand
* super thin controllers
* robust view models
* mustache for ultra dumb templates
* use history API for loading project content: http://html5demos.com/history/
* abstract a separate "pastefolio" core system, create demo app with basic template