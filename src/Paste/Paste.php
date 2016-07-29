<?php
/**
 * Paste - a super simple "CMS" built around static files and folders.
 *
 * @author     Aaron Oxborrow <aaron@pastelabs.com>
 * @link       http://github.com/paste/Paste
 * @copyright  (c) 2016 Aaron Oxborrow
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Paste;

// the main drag
class Paste {

	// full path to where index.php resides, with trailing slash
	public $app_path;

	// full path to content
	public $content_path;

	// content directory relative to app path
	public $content_dir = 'content';

	// content file extension
	public $content_ext = '.html';

	// instance of Mustache engine with string loading for content pages
	public $content_engine;

	// content "database"
	public $content_db;

	// full path to mustache templates
	public $template_path;

	// template directory relative to app path
	public $template_dir = 'templates';

	// template file extension
	public $template_ext = '.stache';

	// main instance of Mustache engine
	public $template_engine;

	// full path to cache for mustache
	public $cache_path;

	// cache directory relative to app path
	public $cache_dir = 'cache';

	// benchmark start
	public $execution_start;

	// base url prefix, defaults to /
	public $base_url = '/';

	// configured routes
	public $routes = array();

	// init Paste with optional config
	public function __construct(array $config = NULL) {

		// start benchmark
		$this->execution_start = microtime(TRUE);

		// use base_url if supplied, enforce leading/trailing slashes
		if (isset($config['base_url']) AND strlen($config['base_url']) > 1)
			$this->base_url = '/'.trim($config['base_url'], '/').'/';

		// use content_dir if supplied
		if (isset($config['content_dir']))
			$this->content_dir = $config['content_dir'];

		// use template_dir if supplied
		if (isset($config['template_dir']))
			$this->template_dir = $config['template_dir'];

		// use cache_dir if supplied
		if (isset($config['cache_dir']))
			$this->cache_dir = $config['cache_dir'];

		// full path to where index.php resides, with trailing slash
		$this->app_path = realpath(getcwd()).'/';

		// full path to content files
		$this->content_path = realpath($this->app_path.$this->content_dir).'/';

		// full path to mustache templates
		$this->template_path = realpath($this->app_path.$this->template_dir).'/';

		// full path to cache for mustache
		$this->cache_path = realpath($this->app_path.$this->cache_dir).'/';

		// setup main mustache engine for templates
		$this->template_engine = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader($this->template_path, array('extension' => $this->template_ext)),
			'cache' => is_writable($this->cache_path) ? $this->cache_path : FALSE,
		));

		// string loader mustache engine for content pages
		$this->content_engine = new \Mustache_Engine(array(
			'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->template_path, array('extension' => $this->template_ext)),
			'cache' => is_writable($this->cache_path) ? $this->cache_path : FALSE,
		));

		// traverse content directory and load all content
		$this->content_db = $this->load_path($this->content_path);

		// post processing, sorting
		$this->content_sorting();

	}

	// init routing and execute
	public function run() {

		// add catch-all default route for Content controller
		$this->add_route('(.*)', function($paste, $url) {
			return $paste->render_url($url);
		});

		// execute routing
		if ($route = $this->routing($this->uri())) {

			// add $paste as first parameter of callback
			array_unshift($route['parameters'], $this);

			// execute routed callback, returning rendered page
			$html = call_user_func_array($route['callback'], $route['parameters']);

		} else {

			// something went sideways -- no matched route -- try 404
			$html = $this->find('404')->render();

		}

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// DEBUG INFOS
		// stop benchmark, get execution time
		$execution_time = number_format(microtime(TRUE) - $this->execution_start, 4);

		// add execution time to HTML
		$benchmark = '<b>execution:</b> '.$execution_time.' sec<br>';

		// add memory usage
		$benchmark .= '<b>memory:</b> '.number_format(round(memory_get_usage(TRUE)/1024, 2)).' KB<br/>';

		// swap in benchmark info and output to browser
		echo str_replace('<!-- benchmark -->', $benchmark, $html);

	}

	// add route(s)
	public function add_route($route, $callback = NULL) {

		// passed an array of routes
		if (is_array($route) AND $callback === NULL) {

			// merge into $routes
			$this->routes = array_merge($this->routes, $route);

		// passed a single route
		} else {

			// set route
			$this->add_route(array($route => $callback));

		}
	}

	// simple router, takes uri and maps arguments
	public function routing($request_uri) {

		// callback and params
		$routed_callback = NULL;
		$routed_parameters = array();

		// match URI against routes and execute glorious vision
		foreach ($this->routes as $route => $callback) {

			// e.g. 'blog/post/([A-Za-z0-9-]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', $request_uri, $params)) {

				// we have a live one
				if (is_callable($callback)) {

					// our route callback
					$routed_callback = $callback;

					// parameters are each of the regex matches
					$routed_parameters = array_slice($params, 1);

					// no need to look further
					break;
				}
			}
		}

		// no route callback matched, return FALSE
		if (empty($routed_callback))
			return FALSE;

		// return matched route / callback
		return array(
			'callback' => $routed_callback,
			'parameters' => $routed_parameters,
		);

	}

	// find URI from REQUEST_URI or CLI
	public function uri() {

		// get requested URI, or from command line argument if running from CLI
		$uri = (PHP_SAPI === 'cli') ? $_SERVER['argv'][1] : getenv('REQUEST_URI');

		// remove query string from URI
		if (FALSE !== $query = strpos($uri, '?'))
			list ($uri, $query) = explode('?', $uri, 2);

		// remove front router (index.php) if it exists in URI
		if (FALSE !== $pos = strpos($uri, 'index.php'))
			$uri = substr($uri, $pos + strlen('index.php'));


		// remove base_url from beginning of URI if it exists
		if (substr($uri, 0, strlen($this->base_url)) == $this->base_url)
			$uri = substr($uri, strlen($this->base_url));

		// remove leading and trailing slashes
		return trim($uri, '/');

	}

	// for simple redirects
	public function redirect($uri = '/') {

		header('Location: '.$uri);
		exit;

	}

	// decipher request and render content page
	public function render_url($url = NULL) {

		// trim slashes
		$url = trim($url, '/');

		// root section
		if (empty($url) OR $url == 'index') {

			// root index
			$page = $this->find(array('parent' => FALSE));

		} else {

			// split up request
			$request = explode('/', $url);

			// find page
			$page = $this->find(array(

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
			$page = $this->find(array('name' => '404'));

			// if no 404 content available, do somethin' sensible
			if ($page === FALSE) {

				// load homepage
				$page = $this->find(array('parent' => FALSE));

				// convert it to a simple 404 page
				$page->title = 'Error 404 - File Not Found';
				$page->content = '<h1>Error 404 - File Not Found</h1>';

			}

		// page redirect configured
		} elseif (! empty($page->redirect)) {

			// redirect to url
			return $this->redirect($page->url());

		}

		// set page to current
		$page->is_current = TRUE;

		// if not a parent, set parent section to current too
		if (! $page->is_parent)
			$page->parent()->is_current_parent = TRUE;

		// render and output the page
		return $page->render();

	}

	// find a single page by properties
	public function find($terms) {

		// get all pages that match
		$pages = $this->query($terms);

		// only return single result
		return empty($pages) ? FALSE : array_shift($pages);

	}

	// filter and return multiple pages by properties
	public function query($terms) {

		// build pages result
		$pages = array();

		// iterage pages, return by reference
		foreach ($this->content_db as $index => &$page) {

			// iterate over search terms
			foreach ($terms as $property => $value) {

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
			}

			// add to pages
			$pages[] = $page;

		}

		// return result
		return $pages;

	}

	// recursively load sections of content
	public function load_path($content_path) {

		$pages = array();

		// iterate over content dir
		foreach ($this->list_path($content_path) as $file) {

			// current pointer
			$path = $content_path.$file;

			// sub directory
			if (is_dir($path))
				$pages = array_merge($pages, $this->load_path($path.'/'));

			// content file with proper extension
			if (is_file($path) AND strpos($file, $this->content_ext)) {

				// able to get file contents
				if (FALSE !== $html = file_get_contents($path)) {

					// load individual content page, process variables
					$content = $this->variables($html);

					// if page has a date variable, convert to timestamp for sorting, otherwise use file modification time
					$content['timestamp'] = empty($content['date']) ? filemtime($path) : strtotime($content['date']);

					// store file path, strip base content path off
					$content['path'] = substr($path, strlen($this->content_path));

					// assign html property
					$content['html'] = $html;

					// instantiate Page object and add to cache
					$pages[] = new Page($this, $content);

				}
			}
		}

		return $pages;

	}

	// list directory in natural sort order
	public function list_path($path) {

		$files = array();

		// open content path for reading
		if (($handle = opendir($path)) === FALSE)
			return $files;

		// iterate content path
		while (($file = readdir($handle)) !== FALSE)
			// ignore dot dirs and paths prefixed with an underscore or period
			if ($file != '.' AND $file != '..' AND $file[0] !== '.')
				$files[] = $file;

		// close handle
		closedir($handle);

		// sort files via natural text comparison, similar to OSX Finder
		usort($files, 'strnatcasecmp');

		// return sorted array (filenames => basenames)
		return $files;

	}

	// process content for embedded variables
	public function variables($html) {

		// match HTML comments that look like
		// <!-- @key: value -->
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

	// post processing on content DB
	public function content_sorting() {

		// record all the pages' master index
		foreach ($this->content_db as $index => &$page)
			$page->_index = $index;

		// look at all parents for sorting requirements
		$sorts = array();
		foreach ($this->content_db as &$page) {

			// must be a parent to apply sorting
			if ($page->is_parent AND ! empty($page->sorting))
				// record sections that need to be sorted
				$sorts[] = $page;

		}

		// sort the children out
		foreach ($sorts as $parent) {

			// get all child pages who call this one mommy
			$children = $this->query(array('parent' => $parent->name));

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
				unset($this->content_db[$child->_index]);

			// append newly sorted pages to end
			$this->content_db = array_merge($this->content_db, $children);

			// update all the pages' master index
			foreach ($this->content_db as $index => &$page)
				$page->_index = $index;

		}
	}

}
