<?php

declare(strict_types=1); defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

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
class Kohana_EncryptTest extends Unittest_TestCase {

	public function setUp(): void
	{
		parent::setUp();

		if ( ! function_exists('mcrypt_encrypt'))
		{
			$this->markTestSkipped('Mcrypt extension is not available.');
		}
	}

	public function test_instance_throw_exception_when_no_key_provided()
	{
		if ( ! function_exists('mcrypt_encrypt'))
		{
			$this->markTestSkipped('Mcrypt extension is not available.');
		}

		$this->expectException('Kohana_Exception');
		$this->expectExceptionMessage('No encryption key is defined in the encryption configuration group');
		Encrypt::instance();
	}

}
