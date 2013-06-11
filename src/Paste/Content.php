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

// Content folder controller
class Content {

	// content "database"
	public static $db;

	// decipher request and render content page
	public static function request($url = NULL) {
		
		// trim slashes
		$url = trim($url, '/');

		// root section
		if (empty($url) OR $url == 'index') {
			
			// root index
			$page = self::find(array('parent' => FALSE));
			
		} else {
			
			// split up request
			$request = explode('/', $url);

			// find page
			$page = self::find(array(
				// current page is always last argument of request
				'name' => array_pop($request),
				
				// current section is 2nd to last argument (ie. parent/parent/page) or 'index' if root page
				'parent' => count($request) == 0 ? 'index' : end($request),
			));
		}
		
		// no page found
		if ($page === FALSE OR ! $page->loaded) {

			// send 404 header
			header('HTTP/1.1 404 File Not Found');

			// draw 404 content if available
			$page = self::find(array('name' => '404'));
			
			// if no 404 content available, do somethin' sensible
			if ($page === FALSE) {
				
				// load homepage
				$page = Page::find(array('parent' => FALSE));

				// convert it to a simple 404 page
				$page->title = 'Error 404 - File Not Found';
				$page->content = '<h1>Error 404 - File Not Found</h1>';
				
			}

		// page redirect configured
		} elseif (! empty($page->redirect)) {

			// redirect to url
			return Paste::redirect($page->url());

		}
		
		// set page to current
		$page->is_current = TRUE;
		
		// if not a parent, set parent section to current too
		if (! $page->is_parent)
			$page->parent()->is_current_parent = TRUE;
		
		// render and output the page
		$page->render(TRUE);
		
	}
	
	// find a single page by properties
	public static function find($terms) {
		
		// get all pages that match
		$pages = self::query($terms);
		
		// only return single result
		return empty($pages) ? FALSE : array_shift($pages);

	}

	// filter and return multiple pages by properties
	public static function query($terms) {
		
		// ensure we have content "DB" loaded
		if (empty(self::$db)) {
			
			// traverse content directory and load all content
			self::$db = self::load_path(Paste::$content_path);
			
			// post processing
			self::sorting();
			
		}

		// store pages here
		$pages = array();

		// iterage pages, return by reference
		foreach (self::$db as $index => &$page) {

			// iterate over search terms
			foreach ($terms as $property => $value)

				// pass array('in:array' => 'value' to search arrays
				if (substr($property, 0, 3) == 'in:') {

					// array to search
					$array = substr($property, 3);
					
					// search array, skip page if value not in array
					if (! isset($page->$array) OR ! in_array($value, $page->$array))
						continue 2;
				
				// skip to next page if property doesn't match
				} elseif (! isset($page->$property) OR $page->$property !== $value) {
					
					// next page
					continue 2;
					
				}

			// add to pages
			$pages[] = $page;

		}
		
		// return result
		return $pages;

	}
	
	// process content for embedded variables
	public static function variables($html) {

		// match HTML comments that look like
		// <!-- @key: value -->
		// http://stackoverflow.com/questions/441404/regular-expression-to-find-and-replace-the-content-of-html-comment-tags/441462#441462
		$regexp = '/<!--((?:[^-]+|-(?!->))*)-->/Ui';
		preg_match_all($regexp, $html, $comments, PREG_OFFSET_CAPTURE);

		// split comments on newline
		$lines = array();
		$offsets = array();
		foreach ($comments[1] as $comment) {
			$lines = array_merge($lines, explode("\n", trim($comment[0])));
			$offsets[] = $comment[1]; // the offset of the comment line if we want to delete them later
		}
		// $offsets = X chars to first var, X chars to start of next var, etc...

		// split lines on colon and assign to key/value
		$vars = array();
		foreach ($lines as $line) {
			if (stristr($line, '@') AND stristr($line, ':')) {
				$parts = explode(":", $line, 2);
				if (count($parts) == 2)
					$vars[trim(str_replace('@', '', $parts[0]))] = trim($parts[1]);
			}
		}

		// convert some values, strip comments
		foreach ($vars as $key => &$value) {
			// convert booleans to native
			if (strtolower($value) === "false" OR $value === '0') {
				$value = FALSE;

			// convert booleans to native
			} elseif (strtolower($value) === "true" OR $value === '1') {
				$value = TRUE;

			// strip any comments from	variables, except redirect
			} elseif ($key !== 'redirect' AND strpos($value, '//')) {
				$value = substr($value, 0, strpos($value, '//'));
			
			}
		}

		return $vars;
	}
	

	// recursively load sections of content
	public static function load_path($content_path) {

		$pages = array();

		// iterate over content dir
		foreach (self::list_path($content_path) as $file) {
			
			// current pointer
			$path = $content_path.$file;
			
			// sub directory
			if (is_dir($path))
				$pages = array_merge($pages, self::load_path($path.'/'));

			// content file with proper extension
			if (is_file($path) AND strpos($file, Paste::$content_ext)) {
				
				// able to get file contents
				if (FALSE !== $html = file_get_contents($path)) {

					// load individual content page, process variables
					$content = self::variables($html);
					
					// if page has a date variable, convert to timestamp for sorting, otherwise use file modification time
					$content['timestamp'] = empty($content['date']) ? filemtime($path) : strtotime($content['date']);
					
					// store file path, strip base content path off
					$content['path'] = substr($path, strlen(Paste::$content_path));

					// assign html property
					$content['html'] = $html;

					// instantiate Page object and add to cache
					$pages[] = Page::create($content);

				}
			}
		}
		
		return $pages;

	}

	// list directory in natural sort order
	public static function list_path($path) {

		$files = array();

		// open content path for reading
		if (($handle = opendir($path)) === FALSE)
			return $files;

		// iterate content path
		while (($file = readdir($handle)) !== FALSE)
			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '_' AND $file[0] !== '.')
				$files[] = $file;
		
		// close handle
		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		usort($files, 'strnatcasecmp');

		// return sorted array (filenames => basenames)
		return $files;

	}
	
	// post processing on content DB
	public static function sorting() {
		
		// record all the pages' master index
		foreach (self::$db as $index => &$page)
			$page->_index = $index;
		
		// look at all parents for sorting requirements
		$sorts = array();
		foreach (self::$db as &$page)
			// must be a parent to apply sorting
			if ($page->is_parent AND ! empty($page->sorting))
				// record sections that need to be sorted
				$sorts[] = $page;
		
		// sort the children out
		foreach ($sorts as $parent) {
		
			// get all child pages who call this one mommy
			$children = self::query(array('parent' => $parent->name));

			// most common sort -- newest first
			if ($parent->sorting == 'date_desc') {
				
				// sort the children by timestamp descending
				usort($children, function($a, $b) {
					if (empty($a->timestamp) AND empty($b->timestamp))
						return TRUE;
					if (! empty($a->timestamp) AND empty($b->timestamp))
						return FALSE;
					if (empty($a->timestamp) AND ! empty($b->timestamp))
						return TRUE;
					return $a->timestamp < $b->timestamp;
				});
			
			// oldest first
			} elseif ($parent->sorting == 'date_asc') {
				
				// sort the children by timestamp ascending
				usort($children, function($a, $b) {
					if (empty($a->timestamp) AND empty($b->timestamp))
						return TRUE;
					if (! empty($a->timestamp) AND empty($b->timestamp))
						return TRUE;
					if (empty($a->timestamp) AND ! empty($b->timestamp))
						return FALSE;
					return $a->timestamp > $b->timestamp;
				});
			}
			
			// unset from master DB index
			foreach ($children as &$child)
				unset(self::$db[$child->_index]);
			
			// append newly sorted pages to end
			self::$db = array_merge(self::$db, $children);
			
			// update all the pages' master index
			foreach (self::$db as $index => &$page)
				$page->_index = $index;
			
		}
	}

	// get base filename without sorting prefix or extension
	public static function base_name($file) {

		// get file name without content extension
		$name = basename($file, Paste::$content_ext);

		// base name is everything after intial period if one exists
		return ($prefix = strpos($name, '.')) ? substr($name, $prefix + 1) : $name;

	}
}