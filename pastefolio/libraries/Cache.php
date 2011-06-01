<?php
/**
 * File based cache library.
 *
 * Adapted from Kohana for Pastefolio
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache {

	// cache directory, must be set before using cache
	public static $directory;

	// Default lifetime of caches in seconds. Specific lifetime can also be set when creating a new cache.
	// Setting this to 0 will never automatically delete caches. Setting this to -1 will disable cache.
	public static $lifetime = 60;

	// Number of cache requests that will be processed before all expired caches are deleted. This is commonly referred to as "garbage collection".
	// Setting this to 0 or a negative number will disable automatic garbage collection.
	public static $requests = 1000;

	// singleton instance
	protected static $instance;

	// for garbage collection
	protected static $loaded;


	// returns a singleton instance of Cache.
	public static function & instance()
	{
		if (self::$instance === NULL)
		{
			// Create a new instance
			self::$instance = new Cache;
		}

		return self::$instance;
	}


	public function __construct() {

		// make sure the cache directory is writable
		if ( ! is_dir(self::$directory) OR ! is_writable(self::$directory))
			die('Cannot write to cache directory');


		if (Cache::$loaded !== TRUE)
		{
			self::$requests = (int) self::$requests;

			if (self::$requests > 0 AND mt_rand(1, self::$requests) === 1)
			{
				// Do garbage collection
				$this->driver->delete_expired();

			}

			// Cache has been loaded once
			Cache::$loaded = TRUE;
		}
	}

	// finds an array of files matching the given id or tag.
	public function exists($id, $tag = FALSE)
	{
		if ($id === TRUE)
		{
			// Find all the files
			return glob(self::$directory.'*~*~*');
		}
		elseif ($tag === TRUE)
		{
			// Find all the files that have the tag name
			$paths = glob(self::$directory.'*~*'.$id.'*~*');

			// Find all tags matching the given tag
			$files = array();
			foreach ($paths as $path)
			{
				// Split the files
				$tags = explode('~', basename($path));

				// Find valid tags
				if (count($tags) !== 3 OR empty($tags[1]))
					continue;

				// Split the tags by plus signs, used to separate tags
				$tags = explode('+', $tags[1]);

				if (in_array($tag, $tags))
				{
					// Add the file to the array, it has the requested tag
					$files[] = $path;
				}
			}

			return $files;
		}
		else
		{
			// Find the file matching the given id
			return glob(self::$directory.$id.'~*');
		}
	}

	// fetches a cache by id. NULL is returned when a cache item is not found.
	// this will delete the item if it is expired or if the hash does not match the stored hash.
	public function get($id)
	{
		// disable caching with negative lifetime
		if (self::$lifetime < 0)
			return NULL;

		// sanitize the ID
		$id = $this->sanitize_id($id);

		if ($file = $this->exists($id))
		{
			// use the first file
			$file = current($file);

			// validate that the cache has not expired
			if ($this->expired($file))
			{
				// remove this cache, it has expired
				$this->delete($id);
			}
			else
			{
				// turn off errors while reading the file
				$ER = error_reporting(0);

				if (($data = file_get_contents($file)) !== FALSE)
				{
					// unserialize the data
					$data = unserialize($data);
				}
				else
				{
					// delete the data
					unset($data);
				}

				// turn errors back on
				error_reporting($ER);
			}
		}

		// Return NULL if there is no data
		return isset($data) ? $data : NULL;
	}



	// fetches all of the caches for a given tag. An empty array will be returned when no matching caches are found.
	public function find($tag)
	{
		// An array will always be returned
		$result = array();

		if ($paths = $this->exists($tag, TRUE))
		{
			// Length of directory name
			$offset = strlen(self::$directory);

			// Find all the files with the given tag
			foreach ($paths as $path)
			{
				// Get the id from the filename
				list($id, $junk) = explode('~', basename($path), 2);

				if (($data = $this->get($id)) !== FALSE)
				{
					// Add the result to the array
					$result[$id] = $data;
				}
			}
		}

		return $result;
	}

	// set a cache item by id. tags may also be added and a custom lifetime can be set. non-string data is automatically serialized.
	function set($id, $data, $tags = NULL, $lifetime = NULL)
	{

		// disable caching with negative lifetime
		if (self::$lifetime < 0)
			return NULL;

		if (is_resource($data))
			die('Cannot cache resource');

		// sanitize the ID
		$id = $this->sanitize_id($id);

		if ($lifetime === NULL)
		{
			// use the default lifetime
			$lifetime = self::$lifetime;
		}

		// cache file driver expects unix timestamp
		$lifetime += time();

		if ( ! empty($tags))
		{
			// convert the tags into a string list
			$tags = implode('+', $tags);
		}

		// write out a serialized cache
		return (bool) file_put_contents(self::$directory.$id.'~'.$tags.'~'.$lifetime, serialize($data));
	}


	// deletes a cache item by id or tag
	public function delete($id, $tag = FALSE)
	{
		// sanitize the ID
		if ($id !== TRUE)
			$id = $this->sanitize_id($id);

		$files = $this->exists($id, $tag);

		if (empty($files))
			return FALSE;

		// disable all error reporting while deleting
		$ER = error_reporting(0);

		foreach ($files as $file)
		{
			// remove the cache file
			if ( ! unlink($file))
				error_log('Cache: Unable to delete cache file: '.$file);
		}

		// turn on error reporting again
		error_reporting($ER);

		return TRUE;
	}


	// check if a cache file has expired by filename.
	protected function expired($file)
	{
		// get the expiration time
		$expires = (int) substr($file, strrpos($file, '~') + 1);

		// expirations of 0 are "never expire"
		return ($expires !== 0 AND $expires <= time());
	}


	// delete all cache items with a given tag
	public function delete_tag($tag)
	{
		return $this->delete($tag, TRUE);
	}

	// delete ALL cache items
	public function delete_all()
	{
		return $this->delete(TRUE);
	}

	// delete expired cache items
	public function delete_expired()
	{
		if ($files = $this->exists(TRUE))
		{
			// disable all error reporting while deleting
			$ER = error_reporting(0);

			foreach ($files as $file)
			{
				if ($this->expired($file))
				{
					// the cache file has already expired, delete it
					if ( ! unlink($file))
						error_log('Cache: Unable to delete cache file: '.$file);
				}
			}

			// turn on error reporting again
			error_reporting($ER);
		}
	}

	// replaces troublesome characters with underscores
	protected function sanitize_id($id)
	{
		// change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}

}
