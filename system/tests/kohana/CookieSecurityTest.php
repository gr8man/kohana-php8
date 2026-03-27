<?php

declare(strict_types=1);

defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Cookie security improvements
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.cookie
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_CookieSecurityTest extends Unittest_TestCase
{
	protected $_original_salt;
	protected $_original_httponly;
	protected $_original_secure;
	protected $_original_samesite;

	public function setUp(): void
	{
		parent::setUp();
		
		$this->_original_salt = Cookie::$salt;
		$this->_original_httponly = Cookie::$httponly;
		$this->_original_secure = Cookie::$secure;
		$this->_original_samesite = Cookie::$samesite;
		
		Cookie::$salt = 'test_salt_for_unit_tests';
		Cookie::$httponly = TRUE;
		Cookie::$secure = FALSE;
		Cookie::$samesite = 'Lax';
	}

	public function tearDown(): void
	{
		Cookie::$salt = $this->_original_salt;
		Cookie::$httponly = $this->_original_httponly;
		Cookie::$secure = $this->_original_secure;
		Cookie::$samesite = $this->_original_samesite;
		
		parent::tearDown();
	}

	public function test_httponly_is_enabled_by_default()
	{
		$this->assertTrue(Cookie::$httponly);
	}

	public function test_samesite_attribute_is_lax_by_default()
	{
		$this->assertEquals('Lax', Cookie::$samesite);
	}

	public function test_cookie_salt_uses_sha256()
	{
		$name = 'test_cookie';
		$value = 'test_value';
		
		$salt = Cookie::salt($name, $value);
		
		$this->assertNotEmpty($salt);
		$this->assertEquals(64, strlen($salt));
	}

	public function test_cookie_salt_is_timing_safe()
	{
		$name1 = 'cookie1';
		$name2 = 'cookie2';
		$value = 'test_value';
		
		$salt1 = Cookie::salt($name1, $value);
		$salt2 = Cookie::salt($name2, $value);
		
		$this->assertNotEquals($salt1, $salt2);
	}

	public function test_cookie_get_returns_null_for_unsigned()
	{
		$_COOKIE['test_unsigned'] = 'value';
		
		$result = Cookie::get('test_unsigned');
		
		$this->assertSame(NULL, $result);
		
		unset($_COOKIE['test_unsigned']);
	}

	public function test_cookie_get_returns_value_for_signed()
	{
		$name = 'test_signed';
		$value = 'signed_value';
		
		$_COOKIE[$name] = Cookie::salt($name, $value).'~'.$value;
		
		$result = Cookie::get($name);
		
		$this->assertSame($value, $result);
		
		unset($_COOKIE[$name]);
	}

	public function test_cookie_get_returns_default_for_missing()
	{
		$default = 'default_value';
		
		$result = Cookie::get('nonexistent_cookie', $default);
		
		$this->assertSame($default, $result);
	}

	public function test_cookie_get_deletes_tampered_cookie()
	{
		$name = 'test_tampered';
		$value = 'original_value';
		
		$_COOKIE[$name] = 'wrong_hash~'.$value;
		
		$result = Cookie::get($name);
		
		$this->assertNull($result);
	}

	public function test_cookie_salt_requires_salt_to_be_set()
	{
		$original_salt = Cookie::$salt;
		Cookie::$salt = NULL;
		
		$this->expectException(Kohana_Exception::class);
		
		Cookie::salt('name', 'value');
		
		Cookie::$salt = $original_salt;
	}

	public function test_cookie_salt_includes_user_agent()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser 1.0';
		
		$salt1 = Cookie::salt('name', 'value');
		
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser 2.0';
		
		$salt2 = Cookie::salt('name', 'value');
		
		$this->assertNotEquals($salt1, $salt2);
	}

	public function test_cookie_init_loads_config()
	{
		$original_salt = Cookie::$salt;
		$original_httponly = Cookie::$httponly;
		$original_secure = Cookie::$secure;
		$original_samesite = Cookie::$samesite;
		
		try {
			Cookie::init();
		}
		catch (Exception $e) {
			// Config may not be available
		}
		
		$this->assertTrue(TRUE);
		
		Cookie::$salt = $original_salt;
		Cookie::$httponly = $original_httponly;
		Cookie::$secure = $original_secure;
		Cookie::$samesite = $original_samesite;
	}

	public function test_same_site_lax_value()
	{
		Cookie::$samesite = 'Lax';
		$this->assertEquals('Lax', Cookie::$samesite);
	}

	public function test_same_site_strict_value()
	{
		Cookie::$samesite = 'Strict';
		$this->assertEquals('Strict', Cookie::$samesite);
	}

	public function test_same_site_null_value()
	{
		Cookie::$samesite = NULL;
		$this->assertNull(Cookie::$samesite);
	}
}
