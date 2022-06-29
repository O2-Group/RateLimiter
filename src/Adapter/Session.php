<?php

namespace O2Group\RateLimit\Adapter;

use Closure;
use O2Group\RateLimit\Adapter\Adapter;

/**
 * This could be changed to just require something implmenting PSR6 - i.e. require a \Cache\CacheItemPoolInterface - but
 * Stash seems to require the 'setInvalidationMethod()' to be called on items....
 */
class Session implements Adapter
{
	private static $session_key = null;
	private static $data = [];

	/**
	 * @param string $sessionKey
	 * @param int $expiration
	 */
	public function __construct(string $sessionKey, int $expiration = 3600)
	{
		$this->setSessionKey($sessionKey);

		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		$this->cleanup($expiration);
	}

	/**
	 * Get the session key
	 *
	 * @return string
	 */
	public static function sessionKey()
	{
		return static::$session_key;
	}

	/**
	 * Set the session key
	 *
	 * @param string
	 * @return void
	 */
	public static function setSessionKey($session_key)
	{
		static::$session_key = $session_key;
	}

	/**
	 * This is the ugliest part of the code.
	 * Come up with a better way to manage it.
	 * @param int $expiration
	 */
	public function cleanup(int $expiration): void
	{
		global $_SESSION;

		if (!$_SESSION) {
			$_SESSION = [];
		}
		if (!array_key_exists(static::sessionKey(), $_SESSION)) {
			$_SESSION[static::sessionKey()] = [];
			return;
		}

		foreach($_SESSION[static::sessionKey()] as $key => $value) {
			$_SESSION[static::sessionKey()][$key] = array_filter($value, function($e) use ($expiration) {
				return $e >= $expiration;
			});
		}
	}

	/**
	 * @param string $key
	 * @param null $default
	 * @return mixed|null
	 */
	public function get(string $key, $default = null)
	{
		global $_SESSION;
		return $this->has($key) ? $_SESSION[static::sessionKey()][$key] : $default;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function set(string $key, $value): bool
	{
		global $_SESSION;
		$_SESSION[static::sessionKey()][$key] = $value;
		return true;
	}


	/**
	 * Get attempt count.
	 * @param $key
	 * @return int|null
	 */
	public function attempts($key) {
		$temp = $this->get($key);
		if (!$temp) {
			return null;
		}
		return count($temp);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		global $_SESSION;
		return array_key_exists($key, $_SESSION[static::sessionKey()]);
	}

	/**
	 * @param string $key
	 * @param int $value
	 * @return bool
	 */
	public function increment($key, $value = 1)
	{
		$entry = static::get($key, null);
		$entry = $entry ? array_merge($entry, array_fill(0, $value, time())) : array_fill(0, $value, time());
		$this->set($key, $entry, 0);
		return true;
	}

	/**
	 * @param string $key
	 * @param int $value
	 * @return false|void
	 */
	public function decrement($key, $value = 1)
	{
		$value = static::get($key, null);
		if (!$value) {
			return false;
		}
		$value['hits'] += $value;
		$this->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function forget($key)
	{
		global $_SESSION;
		unset($_SESSION[static::sessionKey()][$key]);
		return true;
	}

	/**
	 * @return bool
	 */
	public function clear(): bool
	{
		global $_SESSION;
		$_SESSION[static::sessionKey()] = [];
		return true;
	}
}