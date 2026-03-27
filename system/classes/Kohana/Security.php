<?php

declare(strict_types=1);

defined('SYSPATH') OR die('No direct script access.');
/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Security {

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 *     $token = Security::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', Security::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $array->rules('csrf', array(
	 *         array('not_empty'),
	 *         array('Security::check'),
	 *     ));
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @param   boolean $new    force a new token to be generated?
	 * @return  string
	 * @uses    Session::instance
	 */
	public static function token($new = FALSE)
	{
		$session = Session::instance();

		// Get the current token
		$token = $session->get(Security::$token_name);

		if ($new === TRUE OR ! $token)
		{
			// Generate a new unique token
			if (function_exists('openssl_random_pseudo_bytes'))
			{
				// Generate a random pseudo bytes token if openssl_random_pseudo_bytes is available
				// This is more secure than uniqid, because uniqid relies on microtime, which is predictable
				$token = base64_encode(openssl_random_pseudo_bytes(32));
			}
			else
			{
				// Otherwise, fall back to a hashed uniqid
				$token = sha1(uniqid(NULL, TRUE));
			}

			// Store the new token
			$session->set(Security::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if (Security::check($token))
	 *     {
	 *         // Pass
	 *     }
	 *
	 * @param   string  $token  token to check
	 * @return  boolean
	 * @uses    Security::token
	 */
	public static function check($token)
	{
		return Security::slow_equals(Security::token(), $token);
	}
	
	
	
	/**
	 * Compare two hashes in a time-invariant manner.
	 * Prevents cryptographic side-channel attacks (timing attacks, specifically)
	 * 
	 * SECURITY: Uses PHP's hash_equals() when available (PHP 5.6+)
	 * 
	 * @param string $a cryptographic hash
	 * @param string $b cryptographic hash
	 * @return boolean
	 */
	public static function slow_equals($a, $b) 
	{
		if (function_exists('hash_equals'))
		{
			return hash_equals((string) $a, (string) $b);
		}
		
		$diff = strlen((string) $a) ^ strlen((string) $b);
		for($i = 0; $i < strlen((string) $a) AND $i < strlen((string) $b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0; 
	}


	/**
	 * SECURITY: Remove image tags from a string and return the image URL.
	 * 
	 * SECURITY FIX: Properly escapes the URL to prevent XSS attacks
	 * See https://github.com/kohana/kohana/issues/107
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		// SECURITY: Properly extract and escape the image source URL
		// This prevents XSS attacks via malformed img tags
		$str = preg_replace_callback('#<img\s+([^>]*)>#is', function($matches) {
			$attrs = $matches[1];
			
			// Extract src attribute with proper handling of quotes
			if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/', $attrs, $srcMatch)) {
				$url = $srcMatch[1];
				// SECURITY: HTML entity encode the URL
				return HTML::chars($url);
			}
			if (preg_match('/src\s*=\s*([^\s>]+)/', $attrs, $srcMatch)) {
				$url = $srcMatch[1];
				// SECURITY: HTML entity encode the URL
				return HTML::chars($url);
			}
			
			// No src found, return empty string
			return '';
		}, $str);
		
		return $str;
	}

	/**
	 * Encodes PHP tags in a string.
	 *
	 *     $str = Security::encode_php_tags($str);
	 *
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

}
