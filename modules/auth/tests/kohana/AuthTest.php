<?php

declare(strict_types=1);

defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Auth module security improvements
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.auth
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_AuthTest extends Unittest_TestCase
{
	protected $_auth_config;

	public function setUp(): void
	{
		parent::setUp();
		
		$this->_auth_config = array(
			'driver' => 'File',
			'hash_method' => 'sha256',
			'hash_key' => 'test_hash_key_for_unit_tests_only',
			'lifetime' => 1209600,
			'session_type' => 'cookie',
			'session_key' => 'auth_user',
			'bcrypt_cost' => 4,
		);
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	public function test_bcrypt_hash_password()
	{
		$auth = new Auth_File($this->_auth_config);
		
		$password = 'test_password_123';
		$hash = $auth->hash_password($password);
		
		$this->assertNotEmpty($hash);
		$this->assertStringStartsWith('$2', $hash);
		$this->assertTrue(password_verify($password, $hash));
	}

	public function test_bcrypt_different_hashes_for_same_password()
	{
		$auth = new Auth_File($this->_auth_config);
		
		$password = 'test_password_123';
		$hash1 = $auth->hash_password($password);
		$hash2 = $auth->hash_password($password);
		
		$this->assertNotEquals($hash1, $hash2);
		$this->assertTrue(password_verify($password, $hash1));
		$this->assertTrue(password_verify($password, $hash2));
	}

	public function test_check_password_with_bcrypt()
	{
		$auth = new Auth_File($this->_auth_config);
		
		$password = 'my_secure_password';
		$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
		
		$this->assertTrue($auth->check_password($password, $hash));
		$this->assertFalse($auth->check_password('wrong_password', $hash));
	}

	public function test_check_password_with_legacy_hmac()
	{
		$auth = new Auth_File($this->_auth_config);
		
		$password = 'my_secure_password';
		$hash = hash_hmac('sha256', $password, $this->_auth_config['hash_key']);
		
		// Verify the hash is correct
		$this->assertEquals($hash, hash_hmac('sha256', $password, $this->_auth_config['hash_key']));
		
		// Now test check_password
		$this->assertTrue($auth->check_password($password, $hash));
		$this->assertFalse($auth->check_password('wrong_password', $hash));
	}

	public function test_needs_rehash_detects_optimal_cost()
	{
		$auth_config = $this->_auth_config;
		$auth_config['bcrypt_cost'] = 4;
		$auth = new Auth_File($auth_config);
		
		$password = 'test_password';
		$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
		
		// With same cost, should NOT need rehash
		$this->assertFalse($auth->needs_rehash($hash));
	}

	public function test_needs_rehash_detects_wrong_cost()
	{
		$auth_config_low = $this->_auth_config;
		$auth_config_low['bcrypt_cost'] = 4;
		$auth_low = new Auth_File($auth_config_low);
		
		$auth_config_high = $this->_auth_config;
		$auth_config_high['bcrypt_cost'] = 12;
		$auth_high = new Auth_File($auth_config_high);
		
		$password = 'test_password';
		$hash_low_cost = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
		
		// With higher cost, should need rehash
		$this->assertTrue($auth_high->needs_rehash($hash_low_cost));
	}

	public function test_hash_method_exists()
	{
		$auth = new Auth_File($this->_auth_config);
		
		$this->assertTrue(method_exists($auth, 'hash'));
		$this->assertTrue(method_exists($auth, 'hash_password'));
		$this->assertTrue(method_exists($auth, 'check_password'));
		$this->assertTrue(method_exists($auth, 'needs_rehash'));
	}

	public function test_bcrypt_cost_respected()
	{
		$auth_config_low = $this->_auth_config;
		$auth_config_low['bcrypt_cost'] = 4;
		
		$auth = new Auth_File($auth_config_low);
		$hash = $auth->hash_password('test');
		
		$info = password_get_info($hash);
		$this->assertEquals(4, $info['options']['cost']);
	}
	
	public function test_hash_password_returns_different_types()
	{
		$auth = new Auth_File($this->_auth_config);
		
		// bcrypt hash
		$bcrypt_hash = $auth->hash_password('password');
		$this->assertStringStartsWith('$2', $bcrypt_hash);
		
		// Should verify correctly
		$this->assertTrue(password_verify('password', $bcrypt_hash));
	}
}
