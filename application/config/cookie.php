<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie configuration for Kohana
 * 
 * SECURITY: Recommended settings for production use
 * 
 * @link http://kohanaframework.org/guide/about.configuration
 */
return array(
	
	/**
	 * Cookie salt - REQUIRED
	 * Generate a long random string (at least 32 characters)
	 * and set it here. This salt is used to sign cookies.
	 * SECURITY: Default salt for testing only - use a unique value in production!
	 */
	'salt' => 'kohana_test_cookie_salt_for_unit_testing_only_change_in_production',
	
	/**
	 * Cookie expiration time in seconds
	 * 0 = expire when browser closes
	 */
	'expiration' => 0,
	
	/**
	 * Cookie path
	 */
	'path' => '/',
	
	/**
	 * Cookie domain (leave empty for current domain)
	 */
	'domain' => NULL,
	
	/**
	 * Cookie secure (HTTPS only)
	 * SECURITY: Set to TRUE in production if using HTTPS
	 */
	'secure' => FALSE, // Set to TRUE if using HTTPS
	
	/**
	 * Cookie HTTP only (no JavaScript access)
	 * SECURITY: Set to TRUE to prevent XSS attacks via cookies
	 * This prevents JavaScript from accessing the cookie value
	 */
	'httponly' => TRUE,
	
	/**
	 * Cookie SameSite attribute
	 * SECURITY: Helps prevent CSRF attacks
	 * 
	 * Values: 'Strict', 'Lax', or NULL (for none)
	 * 'Lax' is recommended for most applications
	 */
	'samesite' => 'Lax',
	
);
