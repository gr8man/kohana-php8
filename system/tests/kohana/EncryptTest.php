<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the encrypt class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.encrypt
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Samuel Demirdjian <sam@enov.ws>
 * @copyright  (c) 2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_EncryptTest extends Unittest_TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		if (!extension_loaded('openssl')) {
			$this->markTestSkipped('OpenSSL extension is not available.');
		}
	}

	public function test_construct_and_encode_decode(): void
	{
		$key = 'test-encryption-key-32bytes!';
		$encrypt = new Encrypt($key, 'aes-256-cbc');

		$original = 'Hello, World!';
		$encoded = $encrypt->encode($original);

		$this->assertIsString($encoded);
		$this->assertNotSame($original, $encoded);

		$decoded = $encrypt->decode($encoded);
		$this->assertSame($original, $decoded);
	}

	public function test_decode_invalid_base64_returns_false(): void
	{
		$encrypt = new Encrypt('test-key-16-bytes!', 'aes-128-cbc');
		$result = $encrypt->decode('!!!invalid-base64!!!');
		$this->assertFalse($result);
	}

	public function test_decode_truncated_data_returns_false(): void
	{
		$encrypt = new Encrypt('test-key-16-bytes!', 'aes-128-cbc');
		$result = $encrypt->decode('YWJjZA==');
		$this->assertFalse($result);
	}

	public function test_different_keys_produce_different_results(): void
	{
		$encrypt1 = new Encrypt('key-one-16-bytes!', 'aes-128-cbc');
		$encrypt2 = new Encrypt('key-two-16-bytes!!', 'aes-128-cbc');

		$data = 'secret data';
		$encoded1 = $encrypt1->encode($data);
		$encoded2 = $encrypt2->encode($data);

		$this->assertNotSame($encoded1, $encoded2);

		$decoded1 = $encrypt1->decode($encoded1);
		$this->assertSame($data, $decoded1);

		$decoded_with_wrong = $encrypt1->decode($encoded2);
		$this->assertFalse($decoded_with_wrong);
	}
}
