<?php

namespace O2Group\RateLimit;

use O2Group\RateLimit\Adapter\Adapter;
use O2Group\RateLimit\Adapter\Session;

/**
 * Rate limiter.
 */
class Limiter {
    private static $_instance = null;
	private static $_adapter = null;
	private static $_expiration;

    /**
     * Initialize our class.
     */
    public static function getInstance() : Limiter {
        if (static::$_instance === null) {
            $class = get_called_class();
            static::$_instance = new $class();
//            static::$_instance->init();
        }
        return static::$_instance;
    }

    /**
     * Instantiate the singleton
     * @return void
     */
    protected function __construct()
    {
		static::$_expiration = strtotime('-3600 seconds');
		static::getAdapter();
    }

	protected static function getAdapter() : Adapter {
		if (!static::$_adapter) {
			static::$_adapter = new Session('limiter', static::$_expiration);
		}
		return static::$_adapter;
	}

	public static function setExpiration(int $seconds) : void {
//		static::$_expiration =
		static::$_expiration = strtotime('-'.$seconds.' seconds');
	}

	/**
	 * Increment our value
	 * @param string $key
	 * @return bool|int
	 */
	public static function increment(string $key) {
		$key = static::cleanRateLimiterKey($key);
		$value = static::getAdapter()->get($key);
		return static::getAdapter()->increment($key);
	}

	/**
	 * Decrement our value
	 * @param string $key
	 * @return bool|int
	 */
	public static function decrement(string $key) {
		$key = static::cleanRateLimiterKey($key);
		return static::getAdapter()->decrement($key);
	}

	/**
	 * Forget a value.
	 * @param string $key
	 * @return bool
	 */
	public static function forget(string $key) {
		$key = static::cleanRateLimiterKey($key);
		return static::getAdapter()->has($key) ? static::getAdapter()->forget($key) : false;
	}

	/**
	 * Determine if the given key has been "accessed" too many times.
	 *
	 * @param  string  $key
	 * @param  int  $maxAttempts
	 * @return bool
	 */
	public static function tooManyAttempts(string $key, int $maxAttempts = 5) : bool
	{
		$key = static::cleanRateLimiterKey($key);

		if (static::attempts($key) >= $maxAttempts) {
			if (static::getAdapter()->has($key)) {
				return true;
			}
//			static::resetAttempts($key);
		}

		return false;
	}

	/**
	 * Get the number of attempts for the given key.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public static function attempts($key)
	{
		$key = static::cleanRateLimiterKey($key);
		return static::getAdapter()->attempts($key);
	}

	/**
	 * Clean the rate limiter key from unicode characters.
	 *
	 * @param  string  $key
	 * @return string
	 */
	private static function cleanRateLimiterKey($key)
	{
		return preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($key));
	}


	private static function availableAt($delay = 0)
	{
		return $delay instanceof \DateTimeInterface ? $delay->getTimestamp() : strtotime('+'.$delay.' seconds');
	}

}