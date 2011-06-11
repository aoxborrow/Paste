<?php

class cache_controller {

	// request cache key
	public $cache_key;

	public function __construct() {

		// disable cache
		if (isset($_GET['nocache']))
			Cache::$lifetime = FALSE;

		// clear cache
		if (isset($_GET['clearcache']))
			Cache::instance()->delete_all();

		// use requested URI as cache key
		$this->cache_key = empty(Pastefolio::$current_uri) ? 'index' : Pastefolio::$current_uri;

		// try to fetch requested URI from cache
		$this->output = Cache::instance()->get($this->cache_key);

		// validate cache and delete if old
		$this->_validate_cache();

	}

	// TODO: validate cache and delete if old
	public function _validate_cache() {

		if (! empty($this->output)) {
				// validate cache
		}

	}

	// auto render template
	public function _cache() {

		// write to cache
		Cache::instance()->set($this->cache_key, $this->output);

	}

}