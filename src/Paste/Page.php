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
	public $html;

	// mustache template name
	public $template;
	
	// mustache partial template
	public $partial;
	
	// partial has been rendered
	public $partial_rendered;

	// redirect URL for creating aliases
	public $redirect;

	// parent section
	public $parent;
	
	// path to page content
	public $path;
	
	// url to page
	public $url;
	
	// page is a section index
	public $is_parent = FALSE;

	// the currently selected page
	public $is_current = FALSE;

	// the parent of the selected page
	public $is_current_parent = FALSE;
	
	// hide children from menu, but still navigable through prev/next
	public $hide_children = FALSE;

	// takes content details and builds Page object
	public static function create($content = NULL) {
		
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

		// page name is last part
		$name = array_pop($parts);

		// use file base name, unless overidden by variable
		if (empty($page->name))
			$page->name = $name;
		
		// build URL, unless already set by variable
		if (empty($page->url))
			// URL is all the parts put back together + name
			$page->url = str_replace('//', '/', '/'.implode('/', $parts).'/'.$page->name);
		
		// parent is the next remaining part, or for root content it's "index"
		$page->parent = empty($parts) ? 'index' : end($parts);
		
		// the root section has no parents! like Batman...
		if ($page->name == 'index') {
			$page->parent = FALSE;
			$page->url = '/';
		}
		
		// set title to name if not set otherwise
		if (empty($page->title))
			$page->title = ucwords(str_replace(array('_', '-'), ' ', $page->name));
		
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

	// recursive menu items relative to current page
	// returns simple menu heirarchy with:
	// url, title, current, parent, children
	public function menu() {
		
		// build menu basics
		$menu_item = array(
			'url' => $this->url(),
			'label' => $this->label, // -- label in menu
			'title' => $this->title, // -- page title -- used for <a> title
			'current' => $this->is_current,
			'current_parent' => ($this->is_current_parent AND $this->name !== 'index'), // don't set root section as selected
			'parent' => ($this->is_parent OR $this->parent == 'index'), // top pages get section styling
			'parents' => count($this->parents()) - 1, // a simple depth count for menu.stache
			'children' => FALSE,
		);
		
		// add child menu items
		if ($this->is_parent AND ! $this->hide_children) {

			// get all visible child pages who call this one mommy
			$children = Paste::content_query(array('parent' => $this->name, 'visible' => TRUE));
			
			// add to children key
			$menu_item['children'] = array();
		
			// add child menu items recursively
			foreach ($children as $child)
				$menu_item['children'][] = $child->menu();
		}

		// return 
		return $menu_item;

	}
	
	// static function that gets the root index menu
	public static function index() {

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
		if ($this->is_parent AND $this->redirect == 'first_child') {
			
			// get first child page URL
			$first_child = $this->_relative('next');
			
			// has a visible child
			return empty($first_child) ? FALSE : $first_child->url();
			
		}
			
		// URL redirect configured
		if (! empty($this->redirect))
			return $this->redirect;
		
		// no redirect configured, use URL set in constructor
		return $this->url;

	}
	
	// get year (YYYY) from timestamp
	public function year() {
		
		return date('Y', $this->timestamp);

	}
	
	// get month (MM) from timestamp
	public function month() {
		
		return date('m', $this->timestamp);

	}
	
	// get dat (DD) from timestamp
	public function day() {
		
		return date('d', $this->timestamp);

	}
	
	// returns the first paragraph <p> ... </p> of the page
	public function excerpt() {
		
		// location of first opening and closing tags
		$opening = stripos($this->html, "<p>");
		$closing = stripos($this->html, "</p>");
		
		// only proceed if we have both an opening and closing tag
		if ($opening === FALSE OR $closing === FALSE)
			return FALSE;
			
		// return the contents within, tags stripped
		return substr($this->html, $opening, $closing-$opening);

	}
	
	// next sibling page, or cycle to first page in section
	public function next_sibling() {

		// get next page in section, will cycle to first page if needed
		$next = $this->_relative('next', TRUE);

		// return FALSE if we couldn't get the next sibling
		return ($next === FALSE) ? FALSE : $next->url();

	}

	// previous sibling page, or cycle to last page in section
	public function prev_sibling() {

		// get previous page in section, will cycle to last page if needed
		$prev = $this->_relative('prev', TRUE);

		// return FALSE if we couldn't get the previous sibling
		return ($prev === FALSE) ? FALSE : $prev->url();
	}
	
	// next page in global context
	public function next() {

		// get next page in global context, will cycle to first page if needed
		$next = $this->_relative('next');

		// return FALSE if we couldn't get the next page
		return ($next === FALSE) ? FALSE : $next->url();

	}

	// previous page in global context
	public function prev() {

		// get previous page in global context, will cycle to last page if needed
		$prev = $this->_relative('prev');

		// return FALSE if we couldn't get the previous page
		return ($prev === FALSE) ? FALSE : $prev->url();
	}
	
	// recursively build a flat array of pages for context when navigating
	public function _context($siblings_only = FALSE) {
		
		// always array format
		$context = array();
		
		// start with itself
		if ($this->visible)
			$context[] = $this;
		
		// add child pages
		if ($this->is_parent) {
			
			// get all visible child pages who call this one mommy
			$children = Paste::content_query(array('parent' => $this->name, 'visible' => TRUE));
			
			// we found chitlins
			if (! empty($children)) {
				
				// add children recursively to context
				foreach ($children as $child)
					$context = array_merge($context, (($siblings_only) ? array($child) : $child->_context()));
			}
		} 

		// return context
		return $context;

	}

	// returns relative page, based on supplied context.
	// default context is siblings within the current section 
	// global context includes all pages
	public function _relative($offset, $siblings_only = FALSE) {
		
		// context is a flat array of all the other page objects without the current page
		// the pages before the current page are added to the end of the context array
		
		// only the siblings with the current section
		if ($siblings_only) {
		
			// create context from current parent
			$context = $this->parent()->_context(TRUE);
			
		// global context is all content pages flattened
		} else {

			// get root index
			$index = self::find(array('parent' => FALSE));
		
			// build context recursively
			$context = $index->_context();
		}
		
		// store all the pages before and after the current one
		$before = array();
		$after = array();
		$passed = FALSE;
		if (! empty($context)) {
			foreach ($context as $page) {
				// the current page, don't add to context, skip to next
				if ($page === $this) {
					// mark that we're past the current page now
					$passed = TRUE;
				} elseif ($passed) {
					// add page to after
					$after[] = $page;
				} else {
					// add page to before
					$before[] = $page;
				}
			}
		}
		
		// now we can build our context
		$ordered_context = array_merge($after, $before);
		
		// previous page is the last page in context
		if ($offset == 'prev') {
			
			// get the last page in context
			$relative_page = array_pop($ordered_context);
			
			// if the previous page redirects to this one, let's skip it and return the one before that
			if (! empty($relative_page) AND $relative_page->redirect == 'first_child' AND $relative_page === $this->parent())
				$relative_page = array_pop($ordered_context);
		}

		// next page is the first page in context
		if ($offset == 'next')
			$relative_page = array_shift($ordered_context);

		
		// return relative page model or FALSE if it doesn't exist
		return empty($relative_page) ? FALSE : $relative_page;

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
	
	// child pages
	public function children() {
		
		// get all visible child pages who call this one mommy
		return Paste::content_query(array('parent' => $this->name, 'visible' => TRUE));

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
	
	// check if page or parents have a partial
	public function partial() {
		
		// if this page has a partial, just return that
		if (! empty($this->partial))
			return $this->partial;
		
		// don't get partials beyond a template
		// FALSE = don't get parent partials
		if (! empty($this->template) OR $this->partial === FALSE)
			return NULL;

		// iterate over containing parents
		foreach ($this->parents() as $parent) {
			
			// clear partials
			if ($parent->partial === FALSE)
				return NULL; 

			// return parent partial if any
			if (! empty($parent->partial))
				return $parent->partial;
		
			// don't get any more partials beyond a template
			if (! empty($parent->template))
				break;
			
		}

		// no partial
		return NULL;
		
	}

	// prepare content for templates. renders partial template if necessary
	public function content() {
		
		// get any partial defined going up tree
		$partial = $this->partial();

		// no partial, just return HTML
		if (empty($partial) OR $this->partial_rendered) {
			
			// just return content HTML
			return $this->html;
			
		// we have a partial that hasn't been rendered
		} else {
			
			// partial has been rendered
			$this->partial_rendered = TRUE;
			
			// load the partial and render it with Page context
			return Paste::$mustache_engine->render($partial, $this);
			
		}
	}
	
	// render the page with template and partial
	// uses only one template and one partial
	// templates and partials can be set anywhere in parent tree
	// if partial === FALSE or page has a template set, doesn't go further than that
	public function render() {
		
		// get the containing template, the closest defined in parent tree
		$template = $this->template();
		
		// load the template and render it with Page context
		return Paste::$mustache_engine->render($template, $this);
		
	}

}
