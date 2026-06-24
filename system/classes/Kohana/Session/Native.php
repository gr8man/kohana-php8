<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * Native PHP session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Session_Native extends Session
{
	/**
	 * @return false|string
	 */
	#[\Override]
	public function id(): string|false
	{
		return session_id();
	}

	/**
	 * @param   string  $id  session id
	 */
	#[\Override]
	protected function _read($id = null): null
	{
		/**
		 * session_set_cookie_params will override php ini settings
		 * If Cookie::$domain is NULL or empty and is passed, PHP
		 * will override ini and sent cookies with the host name
		 * of the server which generated the cookie
		 *
		 * see issue #3604
		 *
		 * see http://www.php.net/manual/en/function.session-set-cookie-params.php
		 * see http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-domain
		 *
		 * set to Cookie::$domain if available, otherwise default to ini setting
		 */
		$session_cookie_domain = empty(Cookie::$domain)
			? ini_get('session.cookie_domain')
			: Cookie::$domain;

		// Sync up the session cookie with Cookie parameters
		session_set_cookie_params(array(
			'lifetime' => $this->_lifetime,
			'path' => Cookie::$path,
			'domain' => $session_cookie_domain,
			'secure' => Cookie::$secure,
			'httponly' => Cookie::$httponly,
			'samesite' => Cookie::$samesite,
		));

		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter('');

		// Set the session cookie name
		session_name($this->_name);

		if ($id) {
			// Set the session id
			session_id($id);
		}

		// Start the session
		session_start();

		// Use the $_SESSION global for storing data
		$this->_data = & $_SESSION;

		return null;
	}

	/**
	 * @return false|string
	 */
	#[\Override]
	protected function _regenerate(): string|false
	{
		// Regenerate the session id
		session_regenerate_id();

		return session_id();
	}

	#[\Override]
	protected function _write(): bool
	{
		// Write and close the session
		session_write_close();

		return true;
	}

	/**
	 * @return  bool
	 */
	#[\Override]
	protected function _restart()
	{
		// Fire up a new session
		$status = session_start();

		// Use the $_SESSION global for storing data
		$this->_data = & $_SESSION;

		return $status;
	}

	/**
	 * @return  bool
	 */
	#[\Override]
	protected function _destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		$status = ! session_id();

		if ($status) {
			// Make sure the session cannot be restarted
			Cookie::delete($this->_name);
		}

		return $status;
	}

}
