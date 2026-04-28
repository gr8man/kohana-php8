<?php

declare(strict_types=1); defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authorization library. Handles user login and logout, as well as secure
 * password hashing.
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Auth {

	// Auth instances
	protected static $_instance;

	/**
	 * Singleton pattern
	 *
	 * @return Auth
	 */
	public static function instance()
	{
		if ( ! isset(Auth::$_instance))
		{
			// Load the configuration for this type
			$config = Kohana::$config->load('auth');

			if ( ! $type = $config->get('driver'))
			{
				$type = 'file';
			}

			// Set the session class name
			$class = 'Auth_'.ucfirst($type);

			// Create a new session instance
			Auth::$_instance = new $class($config);
		}

		return Auth::$_instance;
	}

	protected $_session;

	protected $_config;

	/**
	 * Loads Session and configuration options.
	 *
	 * @param   array  $config  Config Options
	 * @return  void
	 */
	public function __construct($config = array())
	{
		// Save the config in the object
		$this->_config = $config;

		$this->_session = Session::instance($this->_config['session_type']);
	}

	abstract protected function _login($username, $password, $remember);

	abstract public function password($username);

	/**
	 * Gets the currently logged in user from the session.
	 * Returns NULL if no user is currently logged in.
	 *
	 * @param   mixed  $default  Default value to return if the user is currently not logged in.
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		return $this->_session->get($this->_config['session_key'], $default);
	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   $username  Username to log in
	 * @param   string   $password  Password to check against
	 * @param   boolean  $remember  Enable autologin
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE)
	{
		if (empty($password))
			return FALSE;

		return $this->_login($username, $password, $remember);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  $destroy     Completely destroy the session
	 * @param   boolean  $logout_all  Remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		if ($destroy === TRUE)
		{
			// Destroy the session completely
			$this->_session->destroy();
		}
		else
		{
			// Remove the user from the session
			$this->_session->delete($this->_config['session_key']);

			// Regenerate session_id
			$this->_session->regenerate();
		}

		// Double check
		return ! $this->logged_in();
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string  $role  role name
	 * @return  mixed
	 */
	public function logged_in($role = NULL)
	{
		return ($this->get_user() !== NULL);
	}

	/**
	 * Hash a password using bcrypt (recommended) or fall back to HMAC.
	 * 
	 * SECURITY: Use this method to create password hashes instead of hash().
	 * This method prefers bcrypt (PASSWORD_BCRYPT) when available.
	 *
	 * @param   string  $password  plaintext password
	 * @return  string  hashed password
	 */
	public function hash_password($password)
	{
		if (function_exists('password_hash'))
		{
			$cost = isset($this->_config['bcrypt_cost']) ? (int) $this->_config['bcrypt_cost'] : 12;
			return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
		}

		if ( ! $this->_config['hash_key'])
			throw new Kohana_Exception('A valid hash key must be set in your auth config.');

		return hash_hmac($this->_config['hash_method'], $password, $this->_config['hash_key']);
	}

	/**
	 * Verify a password against a stored hash.
	 * Supports both bcrypt (PHP 5.5+) and legacy HMAC hashes.
	 *
	 * SECURITY: Use this method instead of direct comparison.
	 *
	 * @param   string  $password  plaintext password
	 * @param   string  $hash     stored hash
	 * @return  boolean
	 */
	public function check_password($password, $hash)
	{
		if (preg_match('/^\$2[aby]?\$/', $hash))
		{
			if (function_exists('password_verify'))
			{
				return password_verify($password, $hash);
			}
			throw new Kohana_Exception('bcrypt requires PHP 5.5+ or password_compat library.');
		}

		if ( ! $this->_config['hash_key'])
			throw new Kohana_Exception('A valid hash key must be set in your auth config.');

		$computed = hash_hmac($this->_config['hash_method'], $password, $this->_config['hash_key']);
		return hash_equals($hash, $computed);
	}

	/**
	 * Check if a password hash needs rehashing (for bcrypt).
	 *
	 * @param   string  $hash  stored hash
	 * @return  boolean
	 */
	public function needs_rehash($hash)
	{
		if (function_exists('password_needs_rehash'))
		{
			$cost = isset($this->_config['bcrypt_cost']) ? (int) $this->_config['bcrypt_cost'] : 12;
			return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
		}
		return FALSE;
	}

	/**
	 * Perform a hmac hash, using the configured method.
	 *
	 * @param   string  $str  string to hash
	 * @return  string
	 */
	public function hash($str)
	{
		if ( ! $this->_config['hash_key'])
			throw new Kohana_Exception('A valid hash key must be set in your auth config.');

		return hash_hmac($this->_config['hash_method'], $str, $this->_config['hash_key']);
	}

	protected function complete_login($user)
	{
		// Regenerate session_id
		$this->_session->regenerate();

		// Store username in session
		$this->_session->set($this->_config['session_key'], $user);

		return TRUE;
	}

} // End Auth
