<?php

namespace O2Group\RateLimit\Adapter;

use Closure;

interface Adapter
{
	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key     The unique key of this item in the cache.
	 * @param $default Default value to return if the key does not exist.
	 *
	 * @return The value of the item from the cache, or $default in case of cache miss.
	 *
	 */
	public function get(string $key, $default = null);

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string                 $key   The key of the item to store.
	 * @param                   $value The value of the item to store, must be serializable.
	 * @param $ttl   Optional. The TTL value of this item. If no value is sent and
	 *                                      the driver supports TTL then the library may set a default value
	 *                                      for it or let the driver take care of that.
	 *
	 * @return bool True on success and false on failure.
	 *
	 */
	public function set(string $key, $value): bool;

	/**
	 * Wipes clean the entire cache's keys.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function clear(): bool;

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * NOTE: It is recommended that has() is only to be used for cache warming type purposes
	 * and not to be used within your live applications operations for get/set, as this method
	 * is subject to a race condition where your has() will return true and immediately after,
	 * another script can remove it making the state of your app out of date.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 *
	 */
	public function has(string $key): bool;

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  $value
	 * @return int|bool
	 */
	public function increment($key, $value = 1);

	/**
	 * Decrement the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param   $value
	 * @return int|bool
	 */
	public function decrement($key, $value = 1);

	/**
	 * Remove an item from the cache.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function forget($key);
}