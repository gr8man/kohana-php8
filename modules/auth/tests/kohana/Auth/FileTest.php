<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Auth_File driver
 *
 * @group kohana
 * @group kohana.auth
 * @group kohana.auth.file
 * @package    Kohana/Auth
 * @category   Tests
 */
class Kohana_Auth_FileTest extends Unittest_TestCase
{
	protected array $_auth_config;

	public function setUp(): void
	{
		parent::setUp();
		$this->_auth_config = array(
			'driver'       => 'File',
			'hash_method'  => 'sha256',
			'hash_key'     => 'test_secret_key_123',
			'lifetime'     => 1209600,
			'session_type' => Session::$default,
			'session_key'  => 'auth_user',
			'users'        => array(
				'admin' => hash_hmac('sha256', 'admin_pass', 'test_secret_key_123'),
				'user'  => password_hash('user_pass', PASSWORD_BCRYPT),
			),
		);
	}

	public function tearDown(): void
	{
		Auth::instance()->logout(true, true);
		parent::tearDown();
	}

	public function test_login_success_hmac(): void
	{
		$auth = new Auth_File($this->_auth_config);
		$this->assertTrue($auth->login('admin', 'admin_pass'));
		$this->assertTrue($auth->logged_in());
		$this->assertSame('admin', $auth->get_user());
		$this->assertTrue($auth->check_password('admin_pass'));
		$this->assertFalse($auth->check_password('wrong_pass'));
	}

	public function test_check_password_bcrypt(): void
	{
		$auth = new Auth_File($this->_auth_config);
		$auth->force_login('user');
		$bcrypt_hash = password_hash('user_pass', PASSWORD_BCRYPT);
		$this->assertTrue($auth->check_password('user_pass', $bcrypt_hash));
		$this->assertFalse($auth->check_password('wrong_pass', $bcrypt_hash));
	}

	public function test_login_failure(): void
	{
		$auth = new Auth_File($this->_auth_config);
		$this->assertFalse($auth->login('admin', 'wrong_pass'));
		$this->assertFalse($auth->login('nonexistent', 'any_pass'));
	}

	public function test_force_login_and_logout(): void
	{
		$auth = new Auth_File($this->_auth_config);
		$this->assertTrue($auth->force_login('admin'));
		$this->assertSame('admin', $auth->get_user());
		$this->assertTrue($auth->logout(true, true));
		$this->assertFalse($auth->logged_in());
	}

	public function test_password_retrieval(): void
	{
		$auth = new Auth_File($this->_auth_config);
		$this->assertNotEmpty($auth->password('admin'));
		$this->assertFalse($auth->password('nonexistent'));
	}
}
