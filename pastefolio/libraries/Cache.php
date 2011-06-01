<?php
/**
 * Provides a driver-based interface for finding, creating, and deleting cached
 * resources. Caches are identified by a unique string. Tagging of caches is
 * also supported, and caches can be found and deleted by id or tag.
 *
 * Adapted from Kohana for Pastefolio
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache {

	// singleton instance
	protected static $instance;

	// For garbage collection
	protected static $loaded;

	// Configuration
	protected $config;

	// Driver object
	protected $driver;

	/**
	 * Returns a singleton instance of Cache.
	 *
	 * @param   string  configuration
	 * @return  Cache_Core
	 */
	public static function & instance()
	{
		if (self::$instance === NULL)
		{
			// Create a new instance
			self::$instance = new Cache;
		}

		return self::$instance;
	}

	/**
	 * Loads the configured driver and validates it.
	 *
	 * @param   array|string  custom configuration or config group name
	 * @return  void
	 */
	public function __construct()
	{
		/* Config options
		 *  driver   - Cache backend driver. Kohana comes with file, database, and memcache drivers.
		 *              > File cache is fast and reliable, but requires many filesystem lookups.
		 *              > Database cache can be used to cache items remotely, but is slower.
		 *              > Memcache is very high performance, but prevents cache tags from being used.
		 *
		 *  params   - Driver parameters, specific to each driver.
		 *
		 *  lifetime - Default lifetime of caches in seconds. By default caches are stored for
		 *             thirty minutes. Specific lifetime can also be set when creating a new cache.
		 *             Setting this to 0 will never automatically delete caches.
		 *
		 *  requests - Average number of cache requests that will processed before all expired
		 *             caches are deleted. This is commonly referred to as "garbage collection".
		 *             Setting this to 0 or a negative number will disable automatic garbage collection.
		 */

		// TODO: combine with file driver to minimize code, remove tags?
		// Cache configuration
		$this->config = array(
			'driver'   => 'file',
			'params'   => CACHEPATH,
			'lifetime' => 30,
			'requests' => 1000
		);

		// Load the driver
		//require_once APPPATH.'libraries/Cache/'.$this->config['driver'].'.php';

		// Set driver name
		$driver = 'Cache_'.ucfirst($this->config['driver']).'_Driver';

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		if (Cache::$loaded !== TRUE)
		{
			$this->config['requests'] = (int) $this->config['requests'];

			if ($this->config['requests'] > 0 AND mt_rand(1, $this->config['requests']) === 1)
			{
				// Do garbage collection
				$this->driver->delete_expired();

			}

			// Cache has been loaded once
			Cache::$loaded = TRUE;
		}
	}

	/**
	 * Fetches a cache by id. NULL is returned when a cache item is not found.
	 *
	 * @param   string  cache id
	 * @return  mixed   cached data or NULL
	 */
	public function get($id)
	{
		// Sanitize the ID
		$id = $this->sanitize_id($id);

		return $this->driver->get($id);
	}

	/**
	 * Fetches all of the caches for a given tag. An empty array will be
	 * returned when no matching caches are found.
	 *
	 * @param   string  cache tag
	 * @return  array   all cache items matching the tag
	 */
	public function find($tag)
	{
		return $this->driver->find($tag);
	}

	/**
	 * Set a cache item by id. Tags may also be added and a custom lifetime
	 * can be set. Non-string data is automatically serialized.
	 *
	 * @param   string        unique cache id
	 * @param   mixed         data to cache
	 * @param   array|string  tags for this item
	 * @param   integer       number of seconds until the cache expires
	 * @return  boolean
	 */
	function set($id, $data, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($data))
			die('cannot cache resource');

		// Sanitize the ID
		$id = $this->sanitize_id($id);

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		return $this->driver->set($id, $data, (array) $tags, $lifetime);
	}

	/**
	 * Delete a cache item by id.
	 *
	 * @param   string   cache id
	 * @return  boolean
	 */
	public function delete($id)
	{
		// Sanitize the ID
		$id = $this->sanitize_id($id);

		return $this->driver->delete($id);
	}

	/**
	 * Delete all cache items with a given tag.
	 *
	 * @param   string   cache tag name
	 * @return  boolean
	 */
	public function delete_tag($tag)
	{
		return $this->driver->delete($tag, TRUE);
	}

	/**
	 * Delete ALL cache items items.
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return $this->driver->delete(TRUE);
	}

	/**
	 * Replaces troublesome characters with underscores.
	 *
	 * @param   string   cache id
	 * @return  string
	 */
	protected function sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}

} // End Cache
