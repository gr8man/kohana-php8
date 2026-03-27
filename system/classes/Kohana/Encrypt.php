<?php

declare(strict_types=1);

defined('SYSPATH') OR die('No direct script access.');
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the [OpenSSL](http://php.net/openssl) extension.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Encrypt {

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Encrypt class instances
	 */
	public static $instances = array();

	/**
	 * @var string Encryption key
	 */
	protected $_key;

	/**
	 * @var string openssl method
	 */
	protected $_method;

	/**
	 * @var int the size of the Initialization Vector (IV) in bytes
	 */
	protected $_iv_size;
	
	/**
	 * Returns a singleton instance of Encrypt. An encryption key must be
	 * provided in your "encrypt" configuration file.
	 *
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  $name   configuration group name
	 * @return  Encrypt
	 */
	public static function instance($name = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = Encrypt::$default;
		}

		if ( ! isset(Encrypt::$instances[$name]))
		{
			// Load the configuration data
			$config = Kohana::$config->load('encrypt')->$name;

			if ( ! isset($config['key']))
			{
				// No default encryption key is provided!
				throw new Kohana_Exception('No encryption key is defined in the encryption configuration group: :group',
					array(':group' => $name));
			}

			if ( ! isset($config['method']))
			{
				// Use aes-256-cbc by default as it's modern and secure
				$config['method'] = 'aes-256-cbc';
			}

			// Create a new instance
			Encrypt::$instances[$name] = new Encrypt($config['key'], $config['method']);
		}

		return Encrypt::$instances[$name];
	}

	/**
	 * Creates a new OpenSSL wrapper.
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $method openssl method
	 */
	public function __construct($key, $method)
	{
		$this->_key    = $key;
		$this->_method = $method;

		// Store the IV size
		$this->_iv_size = openssl_cipher_iv_length($this->_method);
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 *     $data = $encrypt->encode($data);
	 *
	 * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param   string  $data   data to be encrypted
	 * @return  string
	 */
	public function encode($data)
	{
		// Generate initialization vector
		$iv = openssl_random_pseudo_bytes($this->_iv_size);

		// Encrypt the data
		$encrypted = openssl_encrypt($data, $this->_method, $this->_key, OPENSSL_RAW_DATA, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$encrypted);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  $data   encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($data)
	{
		// Convert the data back to binary
		$data = base64_decode($data, TRUE);

		if ( ! $data)
		{
			// Invalid base64 data
			return FALSE;
		}

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->_iv_size);

		if ($this->_iv_size !== strlen($iv))
		{
			// The iv is not the expected size
			return FALSE;
		}

		// Remove the iv from the data
		$data = substr($data, $this->_iv_size);

		// Return the decrypted data
		return openssl_decrypt($data, $this->_method, $this->_key, OPENSSL_RAW_DATA, $iv);
	}

}
