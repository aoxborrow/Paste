<?php
/**
 * Simple Tumblr driver for Pastefolio blog
 *
 * Modified version of Evan Walsh's Tumblr class
 * (http://code.evanwalsh.net/Projects/Tumblr).
 *
 * Modified by Alex Dunae (dialect.ca).
 * https://github.com/alexdunae/tumblr-php
*/


class Tumblr {

	// params to pass to filter feed
	public $params = array(
		'type' => 'regular', // If unspecified or empty, all types of posts are returned. (Must be one of $valid_types below)
		'id' => NULL, // A specific post ID to return. Use instead of start, num, or type.
		'start' => NULL, // The post offset to start from. The default is 0.
		'num' => NULL, // The number of posts to return. The default is 20, and the maximum is 50.
		'tagged' => NULL, // Return posts with this tag in reverse-chronological order (newest first).
		'search' => NULL, // Search for posts with this query.
	);

	// convert tumblr properties to more obvious keys
	public $keymap = array(
		'regular-title' => 'title',
		'regular-body' => 'body',
	);

	// valid tumblr post types
	public $post_types = array('regular', 'quote', 'photo', 'link', 'conversation', 'video', 'audio');

	// URL used to access tumblr API
	public $url;


	public function __construct($account = 'demo') {

		// tumblr API URL with text filter set to none
		$this->url = 'http://'.$account.'.tumblr.com/api/read?filter=none&';

	}

	// set parametors for retreiving posts
	public function params($params) {

		foreach ($params as $key => $value) {
			if (array_key_exists($key, $this->params)) {
				$this->params[$key] = $value;
			}
		}

	}

	// retrieve XML posts and convert to flat array
	public function posts() {

		// build query string for tumblr API
		$this->url .= http_build_query($this->params);

		// try to retrieve XML
		if (($xml = simplexml_load_file($this->url)) === FALSE) {
			return FALSE;
		}

		$posts = array();

		foreach ($xml->xpath("/tumblr/posts/post") as $post_xml) {

			$post = array();

			// convert SimpleXMLElement to array, flattening '@attributes'
			foreach ((array) $post_xml as $key => $value) {

				if ($key == '@attributes') {
					// flatten '@attributes' into post array
					$post = array_merge($post, $value);
				} else {
					// change some key names according to keymap
					if (array_key_exists($key, $this->keymap)) {
						$key = $this->keymap[$key];
					}
					$post[$key] = $value;
				}
			}

			$posts[] = $post;

		}

		return $posts;

	}


	/**
	* Set up read cache.
	*
	* @param int	 $duration	 Number of seconds to cache data
	* @param string	 $path		 Where to store the cache files (e.g. '_cache/')
	*/
	function init_cache($duration, $path = '') {
		$this->_cache_duration = $duration;
		$this->_cache_path = $path;
	}


	function read($url,$json = false){
			$output = $this->_read_from_cache($url, $json);

			if(!empty($output))
				return $output;

			$url = "$url/api/read";
			if($json){
					$url .= "/json";
			}

			//$url .= '?filter=text';
			if(ini_get("allow_url_fopen")){
					$output = file_get_contents($url);
					$this->_save_to_cache($url, $json, $output);
			}
			elseif(function_exists("curl_version")){
					$c = curl_init($url);
					curl_setopt($c,CURLOPT_HEADER,1);
					curl_setopt($c,CURLOPT_RETURNTRANSFER,1);
					$output = curl_exec($c);
					$this->_save_to_cache($url, $json, $output);
			}
			else{
					$output = "error: cannot fetch file";
			}
			return $output;
	}

	/**
	* Attempt to read the results of a read request from the cache.
	*
	* @returns string Either an empty string or the cached data
	*/
	function _read_from_cache($url, $json) {
		// no caching
		if(!$this->_cache_duration)
			return '';

		$cache_file = $this->_cache_path . 'tumblr-' . md5($url . $json) . '.js';

		$cache_created = (@file_exists($cachefile))? @filemtime($cachefile) : 0;
		clearstatcache();

		// cache has expired
		if (time() - $this->_cache_duration > $cache_created)
			return '';


		$output = @file_get_contents($cache_file, false);

		return ($res === false ? '' : $output);
	}


	/**
	* Save the results of a read request.
	*
	* @returns bool
	*/
	function _save_to_cache($url, $json, $data) {
		// no caching
		if(!$this->_cache_duration) return;

		$cache_file = $this->_cache_path . 'tumblr-' . md5($url . $json) . '.js';

		$res = @file_put_contents($cache_file, $data, FILE_TEXT | LOCK_EX);

	}
}