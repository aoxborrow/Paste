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
		'filter' => 'none', // Text filter
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
		$this->url = 'http://'.$account.'.tumblr.com/api/read?';

	}

	// retrieve XML posts and convert to flat array
	public function posts($params) {

		foreach ($params as $key => $value) {
			if (array_key_exists($key, $this->params)) {
				$this->params[$key] = $value;
			}
		}

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



}