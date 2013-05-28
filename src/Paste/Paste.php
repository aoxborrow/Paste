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

// the main drag
class Paste {

	// full path to where index.php resides, with trailing slash
	public static $app_path;
	
	// full path to content
	public static $content_path;

	// content directory relative to app path
	public static $content_dir = 'content';
	
	// content file extension
	public static $content_ext = '.html';
	
	// content "database"
	public static $content_cache;

	// full path to mustache templates
	public static $template_path;

	// template directory relative to app path
	public static $template_dir = 'templates';

	// template file extension
	public static $template_ext = '.stache';

	// request uri
	public static $request_uri;
	
	// configured routes
	public static $routes = array();

	// init and execute
	public function run() {

		// start benchmark
		$execution_start = microtime(TRUE);

		// full path to where index.php resides, with trailing slash
		self::$app_path = rtrim(getcwd(), '/').'/';
		
		// full path to content files
		self::$content_path = self::$app_path.self::$content_dir.'/';
	
		// full path to mustache templates
		self::$template_path = self::$app_path.self::$template_dir.'/';

		// detect URI
		self::$request_uri = self::uri();

		// add catch-all default route for Page controller
		self::route('(.*)', 'self::content_request');
		
		// execute routing 
		if ($route = self::routing()) {

			// execute routed callback
			call_user_func_array($route['callback'], $route['parameters']);

		} else {
		
			// something went sideways -- no matched route -- try 404
			self::find_page('404');
			
		}
		
		// DEBUG INFOS
		// stop benchmark, get execution time
		$execution_time = number_format(microtime(TRUE) - $execution_start, 4);

		// add benchmark time to end of HTML
		echo PHP_EOL.'<br/>Execution Time: '.$execution_time.', Included Files: '.count(get_included_files()).', Memory Usage: '.number_format(round(memory_get_usage(TRUE)/1024, 2)).'KB';
		

	}

	// simple router, takes uri and maps arguments
	public static function routing() {
		
		// callback and params
		$routed_callback = NULL;
		$routed_parameters = array();
		
		// match URI against routes and execute glorious vision
		foreach (self::$routes as $route => $callback) {
			
			// e.g. 'blog/post/([A-Za-z0-9]+)' => 'blog/post/$1'
			// match URI against keys of defined $routes
			if (preg_match('#^'.$route.'$#u', self::$request_uri, $params)) {
				
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
	
	// add route(s)
	public static function route($route, $callback = NULL) {
		
		// passed an array of routes
		if (is_array($route) AND $callback === NULL) {
			
			// merge into $routes
			self::$routes = array_merge(self::$routes, $route);
		
		// passed a single route
		} else {
			
			// set route
			self::route(array($route => $callback));
			
		}
	}
	
	// find URI from REQUEST_URI or CLI
	public static function uri() {

		// get requested URI, or from command line argument if running from CLI
		$uri = (PHP_SAPI === 'cli') ? $_SERVER['argv'][1] : getenv('REQUEST_URI');
		
		// remove query string from URI
		if (FALSE !== $query = strpos($uri, '?'))
			list ($uri, $query) = explode('?', $uri, 2);

		// remove front router (index.php) if it exists in URI
		if (FALSE !== $pos = strpos($uri, 'index.php'))
			$uri = substr($uri, $pos + strlen('index.php'));

		// remove leading and trailing slashes
		return trim($uri, '/');

	}

	// for simple redirects
	public static function redirect($uri = '/') {

		header('Location: '.$uri);
		exit;

	}
	
	// decipher request and render content page
	public static function content_request($url = NULL) {
		
		// trim slashes
		$url = trim($url, '/');

		// root section
		if (empty($url) OR $url == 'index') {
			
			// root index
			$page = Page::find(array('parent' => FALSE));
			
		} else {
			
			// split up request
			$request = explode('/', $url);

			// find page
			$page = Page::find(array(
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
			$page = Page::find(array('name' => '404'));
			
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
			return self::redirect($page->url());

		}
		
		// set page to current
		$page->is_current = TRUE;
		
		// if not a parent, set parent section to current too
		if (! $page->is_parent)
			$page->parent()->is_current = TRUE;

		// send text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');
		
		// render the page
		echo $page->render();

	}

	// filter and return pages by properties
	// when $first, only return first result, not in an array
	public static function content_query($terms) {
		
		// ensure we have content "DB" loaded
		if (empty(self::$content_cache)) {
			
			// traverse content directory and load all content
			self::$content_cache = self::content_load(self::$content_path);
			
		}

		// store pages here
		$pages = array();

		// iterage pages, return by reference
		foreach (self::$content_cache as &$page) {

			// iterate over search terms
			foreach ($terms as $property => $value)
				// skip to next page if property doesn't match
				if ($page->$property !== $value)
					continue 2;

			// add to pages
			$pages[] = $page;

		}
		
		// return FALSE if no pages found
		return empty($pages) ? FALSE : $pages;

	}
	
	// process content for embedded variables
	public static function content_process($html) {

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
	public static function content_load($content_path) {

		$pages = array();

		// iterate over content dir
		foreach (self::content_list($content_path) as $file) {
			
			// current pointer
			$path = $content_path.$file;
			
			// sub directory
			if (is_dir($path))
				$pages = array_merge($pages, self::content_load($path.'/'));

			// content file with proper extension
			if (is_file($path) AND strpos($file, self::$content_ext)) {
				
				// able to get file contents
				if (FALSE !== $html = file_get_contents($path)) {

					// load individual content page, process variables
					$content = self::content_process($html);
					
					// store file path, strip base content path off
					$content['path'] = substr($path, strlen(Paste::$content_path));

					// assign html to content property
					$content['content'] = $html;

					// instantiate Page object and add to cache
					$pages[] = Page::from_content($content);

				}
			}
		}
		
		return $pages;

	}

	// list directory in natural sort order
	public static function content_list($path) {

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

	// get base filename without sorting prefix or extension
	public static function content_name($file) {

		// get file name without content extension
		$name = basename($file, self::$content_ext);

		// base name is everything after intial period if one exists
		return ($prefix = strpos($name, '.')) ? substr($name, $prefix + 1) : $name;

	}

	
	public static function debug() {
		
		// get index section
		$index = Page::find(array('parent' => FALSE));
		
		// build full menu from index section
		$menu = $index->menu();
		
		// output menu with Pre
		echo Pre::render($menu);

	}
	
}