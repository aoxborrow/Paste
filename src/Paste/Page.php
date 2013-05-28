<?php 
/**
 * Paste - a super simple "CMS" built around static files and folders.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://github.com/paste/Paste
 * @copyright  (c) 2013 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Paste;

// page model
class Page {
	
	// page successfully loaded
	public $loaded = FALSE;
	
	// visible in menu
	public $visible = TRUE;

	// page name and link slug
	public $name;
	
	// page title
	public $title;

	// label in menu
	public $label;

	// page content
	public $content;

	// mustache template name
	public $template;
	
	// mustache partial template
	public $partial;

	// redirect URL for creating aliases
	public $redirect;

	// parent section
	public $parent;
	
	// path to page content
	public $path;
	
	// url to page
	public $url = '/';
	
	// page is a section index
	public $is_parent = FALSE;

	// the currently selected page
	public $is_current = FALSE;

	//  takes content details and builds Page object
	public static function from_content($content = NULL) {
		
		// instantiate Page model
		$page = new Page;

		// assign content data to page model
		foreach ($content as $key => $value)
			$page->$key = $value;
		
		// parts array is content path after stripping prefix and extensions from each part
		$parts = array_map('Paste\Paste::content_name', explode('/', $page->path));
		
		// sections are represented by their index file
		if (end($parts) === 'index') {
			
			// parent section
			$page->is_parent = TRUE;
			
			// remove 'index' from parts if deeper than root
			if (count($parts) > 1)
				$parts = array_slice($parts, 0, -1);
			
		} 

		// URL is all the parts put back together
		$page->url .= implode('/', $parts);

		// page name is last part
		$page->name = array_pop($parts);
	
		// parent is the next remaining part, or for root content it's "index"
		$page->parent = empty($parts) ? 'index' : end($parts);
		
		// the root section has no parents! like Batman...
		if ($page->name == 'index')
			$page->parent = FALSE;
		
		// set title to name if not set otherwise
		if (empty($page->title))
			$page->title = ucwords(str_replace('_', ' ', $page->name));
		
		// use title for menu label if not specified
		if (empty($page->label))
			$page->label = $page->title;
		
		// page is loaded
		$page->loaded = TRUE;

		// return Page object
		return $page;

	}
	
	// find a single page by properties
	public static function find($terms) {
		
		// get all pages that match
		$pages = Paste::content_query($terms);
		
		// only return single result
		return empty($pages) ? FALSE : array_shift($pages);

	}

	// menu items relative to current page
	// returns simple menu heirarchy with:
	// url, title, current, parent, children
	public function menu() {
		
		// build menu basics
		$menu_item = array(
			'url' => $this->url(),
			'label' => $this->label, // -- label in menu
			'title' => $this->title, // -- page title -- used for <a> title
			'current' => $this->is_current,
			'parent' => ($this->is_parent OR $this->parent == 'index'), // top pages get section styling
			'parents' => count($this->parents()) - 1, // a simple depth count for the menu.stache
			'children' => FALSE,
		);
		
		// add child menu items
		if ($this->is_parent) {
			
			// add to children key
			$menu_item['children'] = array();
			
			// get all visible child pages who call this one mommy
			$children = Paste::content_query(array('parent' => $this->name, 'visible' => TRUE));

			// add child menu items recursively
			foreach ($children as $child)
				$menu_item['children'][] = $child->menu();
		}

		// return 
		return $menu_item;

	}
	
	// static function that gets the root index menu
	public static function index_menu() {

		// get index section
		$index = self::find(array('parent' => FALSE));
		
		// build full menu from index section
		$menu = $index->menu();

		// if visible show root index page in menu, otherwise just return children
		return ($index->visible) ? $menu : $menu['children'];

	}
	
	// build full URL based on parents, or use defined redirect
	public function url() {

		// parent configured to redirect to first child
		if ($this->is_parent AND $this->redirect == 'first_child')
			// get first child page URL
			return $this->_relative('first', $this->name)->url();
			
		// URL redirect configured
		if (! empty($this->redirect))
			return $this->redirect;
		
		// no redirect configured, use URL set in constructor
		return $this->url;

	}
	
	// next sibling page, or cycle to first page
	public function next() {

		// get next page in section
		$next = $this->_relative(1);

		// cycle to first page if last in section
		return ($next === FALSE) ? $this->_relative('first')->url() : $next->url();

	}

	// previous sibling page, or cycle to last page
	public function prev() {

		// get previous page in section
		$prev = $this->_relative(-1);

		// cycle to last page if first in section
		return ($prev === FALSE) ? $this->_relative('last')->url() : $prev->url();
	}

	// returns relative page, uses current section by default
	public function _relative($offset = 0, $parent = NULL) {
		
		// use current section if not supplied
		if ($parent === NULL)
			$parent = $this->parent;

		// create page map from current section
		$section = Paste::content_query(array('parent' => $parent, 'visible' => TRUE));
		
		// build simple array of names
		$siblings = array();
		foreach ($section as $sibling)
			$siblings[] = $sibling->name;
		
		// numeric offset
		if (is_numeric($offset)) {
		
			// find current key
			$current_index = array_search($this->name, $siblings);

			// desired offset exists, use that
			$relative_page = isset($siblings[$current_index + $offset]) ? $siblings[$current_index + $offset] : FALSE;

		// first sibling
		} elseif ($offset == 'first') {
			
			$relative_page = array_shift($siblings);
			
		// last sibling	
		} elseif ($offset == 'last') {
			
			$relative_page = array_pop($siblings);

		}
		
		// return relative page model or FALSE if it doesn't exist
		return empty($relative_page) ? FALSE : self::find(array('name' => $relative_page));

	}
	
	// get the immediate parent object
	public function parent() {
		
		// get parents
		$parents = $this->parents();
		
		// return first parent
		return array_shift($parents);
		
	}
	
	// get all parents in an array
	public function parents() {

		// start the recursion
		$parent = $this;

		// init
		$parents = array();

		// add parents while possible
		while ($parent) {
			
			// get parent page
			$parent = self::find(array('name' => $parent->parent, 'is_parent' => TRUE));

			// add to list
			$parents[] = $parent;

		}
		
		// return list of parents
		return $parents;

	}
	
	// return closest template in parent tree
	public function template() {
		
		// if this page has a template, just return that
		if (! empty($this->template))
			return $this->template;

		// iterate over containing parents
		foreach ($this->parents() as $parent) {
			
			// return parent template if any
			if (! empty($parent->template))
				return $parent->template;

		}
		
		// no template
		return NULL;
		
	}

	// return array of cascading partials
	public function partials() {
		
		// init array
		$partials = array();
		
		// add page partial as first element
		if (! empty($this->partial))
			$partials[] = $this->partial;
		
		// only look at parent partials if this page doesn't have a template
		if (empty($this->template)) {
		
			// iterate over containing parents
			foreach ($this->parents() as $parent) {
				
				// add parent partial if any
				if (! empty($parent->partial))
					$partials[] = $parent->partial;
			
				// don't get any more partials beyond a template
				if (! empty($parent->template))
					break;
			}
		}

		// remove any duplicates and return array
		return array_unique($partials);

	}
	
	// render the page with template and partials
	// uses only one template, then cascading, compiled partials
	public function render() {
		
		// get the containing template, the closest defined in parent tree
		$template = $this->template();
		
		// no template defined, use content placeholder
		if (empty($template)) {
			
			// placeholder to swap partials or content into
			$template = '{{{content}}}';
			
		// we have a template
		} else {
			
			// templates_path - template name - file extension
			$template_path = Paste::$template_path.$template.Paste::$template_ext;
			
			// load template file 
			$template = file_get_contents(realpath($template_path));
			
		}
		
		// get any partials going up tree
		$partials = $this->partials();
		
		// we have partials, fold them into each other
		if (! empty($partials)) {
			
			// iterate over partials and merge into template
			foreach ($partials as $partial_template) {
			
				// templates_path - partial name - file extension
				$partial_path = Paste::$template_path.$partial_template.Paste::$template_ext;
				
				// load partial file 
				$partial_template = file_get_contents(realpath($partial_path));
			
				// merge one partial into another via the {{{content}}} string
				$template = str_replace('{{{content}}}', $partial_template, $template);
			
			}
		}

		// now we should have a template string that includes any/all partials folded into it
		// we still support additional mustache partials via partial_loader
		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_StringLoader,
			'partials_loader' => new \Mustache_Loader_FilesystemLoader(Paste::$template_path, array('extension' => Paste::$template_ext)),
			// 'cache' => Paste::$app_path.'cache',
		));

		// load the compiled template via StringLoader
		$template = $mustache->loadTemplate($template);

		// render the template with this context
		return $template->render($this);

	}

	
	// render the page with templates
	// --  uses the first two possible templates, everything else must be partials
	public function render_dynamic() {
		
		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader(Paste::$template_path, array('extension' => Paste::$template_ext)),
			// 'cache' => Paste::$app_path.'cache',
		));
		
		// this allows dynamic partials
		// https://github.com/bobthecow/mustache.php/pull/101
		$mustache->addHelper('partial_render', function($text, $mustache) {
			return "{{>".$mustache->render($text).'}}';
		});

		// get all the templates
		$templates = $this->templates();
		
		// load the main template
		$template = $mustache->loadTemplate($templates[0]);

		// set next template as partial
		if (! empty($templates[1]))
			$this->partial = $templates[1];
		
		// render template
		return $template->render($this);

	}
	
	// render the page with templates
	// -- uses string manip to combine all possible templates before rendering
	public function render_cascade() {
		
		// TODO: instantiate engine and cache in Paste?
		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_StringLoader,
			'partials_loader' => new \Mustache_Loader_FilesystemLoader(Paste::$template_path, array('extension' => Paste::$template_ext)),
			// 'cache' => Paste::$app_path.'cache',
		));
		
		// placeholder
		$template = '{{{content}}}';

		// iterate over templates and merge together
		foreach ($this->templates() as $parent_template) {
			
			// directory where template files are stored - template name - file extension
			$template_path = Paste::$template_path.$parent_template.Paste::$template_ext;

			// load template file 
			$parent_template = file_get_contents(realpath($template_path));
			
			// merge one template into another via the {{{content}}} string
			$template = str_replace('{{{content}}}', $parent_template, $template);

		}

		$template = $mustache->loadTemplate($template);
		return $template->render($this);

	}

}
