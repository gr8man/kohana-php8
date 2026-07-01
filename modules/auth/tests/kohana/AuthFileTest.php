<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Auth_File driver
 *
 * @group kohana
 * @group kohana.auth
 * @group kohana.auth.file
 *
 * @package    Kohana/Auth
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_AuthFileTest extends Unittest_TestCase
{
	protected $_config;

	public function setUp(): void
	{
		parent::setUp();

		$this->_config = array(
			'driver' => 'File',
			'hash_method' => 'sha256',
			'hash_key' => 'test_hash_key_for_unit_tests',
			'lifetime' => 1209600,
			'session_type' => 'cookie',
			'session_key' => 'auth_user',
			'bcrypt_cost' => 4,
			'users' => array(
				'admin' => '21232f297a57a5a743894a0e4a801fc3',
				'user' => 'ee11cbb19052e40b07aac0ca060c23ee',
			),
		);
	}

	public function test_instance_returns_auth_file(): void
	{
		$auth = new Auth_File($this->_config);
		$this->assertInstanceOf(Auth_File::class, $auth);
		$this->assertInstanceOf(Auth::class, $auth);
	}

	public function test_password_returns_hash_for_existing_user(): void
	{
		$auth = new Auth_File($this->_config);
		$password = $auth->password('admin');
		$this->assertSame('21232f297a57a5a743894a0e4a801fc3', $password);
	}

	public function test_password_returns_false_for_missing_user(): void
	{
		$auth = new Auth_File($this->_config);
		$this->assertFalse($auth->password('nonexistent'));
	}

	public function test_hash_uses_configured_method(): void
	{
		$auth = new Auth_File($this->_config);
		$hash = $auth->hash('test_password');
		$expected = hash_hmac('sha256', 'test_password', 'test_hash_key_for_unit_tests');
		$this->assertSame($expected, $hash);
	}

	public function test_hash_without_key_throws(): void
	{
		$this->expectException(ErrorException::class);
		$auth = new Auth_File(array(
			'driver' => 'File',
			'hash_method' => 'sha256',
			'session_type' => 'cookie',
		));
		$auth->hash('test');
	}

	public function test_hash_without_session_type_throws(): void
	{
		$this->expectException(ErrorException::class);
		$auth = new Auth_File(array(
			'driver' => 'File',
			'hash_method' => 'sha256',
		));
		$auth->hash('test');
	}

	public function test_hash_password_uses_bcrypt(): void
	{
		$auth = new Auth_File($this->_config);
		$hash = $auth->hash_password('test_password');

		$this->assertNotEmpty($hash);
		$this->assertStringStartsWith('$2', $hash);
		$this->assertTrue(password_verify('test_password', $hash));
	}

	public function test_hash_password_respects_cost(): void
	{
		$config = $this->_config;
		$config['bcrypt_cost'] = 6;
		$auth = new Auth_File($config);

		$hash = $auth->hash_password('test');
		$info = password_get_info($hash);

		$this->assertEquals(6, $info['options']['cost']);
	}

	public function test_check_password_bcrypt(): void
	{
		$auth = new Auth_File($this->_config);
		$hash = password_hash('my_password', PASSWORD_BCRYPT, array('cost' => 4));

		$this->assertTrue($auth->check_password('my_password', $hash));
		$this->assertFalse($auth->check_password('wrong_password', $hash));
	}

	public function test_check_password_legacy_hmac(): void
	{
		$auth = new Auth_File($this->_config);
		$hash = hash_hmac('sha256', 'password', 'test_hash_key_for_unit_tests');

		$this->assertTrue($auth->check_password('password', $hash));
		$this->assertFalse($auth->check_password('wrong_password', $hash));
	}

	public function test_check_password_without_hash_key_throws(): void
	{
		$this->expectException(ErrorException::class);
		$auth = new Auth_File(array(
			'driver' => 'File',
			'hash_method' => 'sha256',
			'session_type' => 'cookie',
		));
		$auth->check_password('password', 'legacy_hash');
	}

	public function test_check_password_returns_false_when_not_logged_in(): void
	{
		$auth = $this->getMockBuilder('Auth_File')
			->setConstructorArgs(array($this->_config))
			->onlyMethods(array('get_user'))
			->getMock();

		$auth->method('get_user')->willReturn(false);

		$result = $auth->check_password('password');
		$this->assertFalse($result);
	}

	public function test_needs_rehash_returns_false_for_matching_cost(): void
	{
		$auth = new Auth_File($this->_config);
		$hash = password_hash('test', PASSWORD_BCRYPT, array('cost' => 4));

		$this->assertFalse($auth->needs_rehash($hash));
	}

	public function test_needs_rehash_returns_true_for_different_cost(): void
	{
		$config = $this->_config;
		$config['bcrypt_cost'] = 10;
		$auth = new Auth_File($config);

		$hash = password_hash('test', PASSWORD_BCRYPT, array('cost' => 4));
		$this->assertTrue($auth->needs_rehash($hash));
	}

	public function test_logged_in_returns_false_when_not_logged_in(): void
	{
		$auth = new Auth_File($this->_config);
		$this->assertFalse($auth->logged_in());
	}

	public function test_force_login_sets_user(): void
	{
		$auth = $this->getMockBuilder('Auth_File')
			->setConstructorArgs(array($this->_config))
			->onlyMethods(array('complete_login'))
			->getMock();

		$auth->expects($this->once())
			->method('complete_login')
			->with('admin')
			->willReturn(true);

		$result = $auth->force_login('admin');
		$this->assertTrue($result);
	}

	public function test_login_with_empty_password_returns_false(): void
	{
		$auth = new Auth_File($this->_config);

		$refl = new ReflectionClass($auth);
		$method = $refl->getMethod('_login');

		$result = $method->invoke($auth, 'admin', '', false);
		$this->assertFalse($result);
	}

	public function test_hash_methods_exist(): void
	{
		$auth = new Auth_File($this->_config);

		$this->assertTrue(method_exists($auth, 'hash'));
		$this->assertTrue(method_exists($auth, 'hash_password'));
		$this->assertTrue(method_exists($auth, 'check_password'));
		$this->assertTrue(method_exists($auth, 'needs_rehash'));
		$this->assertTrue(method_exists($auth, 'force_login'));
		$this->assertTrue(method_exists($auth, 'password'));
	}

	public function test_bcrypt_hash_different_each_time(): void
	{
		$auth = new Auth_File($this->_config);
		$password = 'same_password';

		$hash1 = $auth->hash_password($password);
		$hash2 = $auth->hash_password($password);

		$this->assertNotEquals($hash1, $hash2);
		$this->assertTrue(password_verify($password, $hash1));
		$this->assertTrue(password_verify($password, $hash2));
	}

	public function test_login_delegates_to__login(): void
	{
		$auth = $this->getMockBuilder('Auth_File')
			->setConstructorArgs(array($this->_config))
			->onlyMethods(array('_login'))
			->getMock();

		$auth->expects($this->once())
			->method('_login')
			->with('admin', 'password', false)
			->willReturn(true);

		$result = $auth->login('admin', 'password');
		$this->assertTrue($result);
	}
}
