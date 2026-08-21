<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Session_Cookie
 *
 * @group kohana
 * @group kohana.session
 * @group kohana.session.cookie
 * @package    Kohana
 * @category   Tests
 */
class Kohana_Session_CookieTest extends Unittest_TestCase
{
	protected Session_Cookie $session;

	public function setUp(): void
	{
		parent::setUp();
		Cookie::$salt = 'test_salt_session_cookie_test';
		$this->session = new Session_Cookie(array('name' => 'test_cookie_sess'));
	}

	public function test_get_set_delete(): void
	{
		$this->session->set('key1', 'val1');
		$this->assertSame('val1', $this->session->get('key1'));

		$this->session->delete('key1');
		$this->assertNull($this->session->get('key1'));
	}

	public function test_write_and_restart(): void
	{
		$this->session->set('user', 'bob');
		$this->assertTrue($this->session->write());
		$this->assertTrue($this->session->restart());
	}

	public function test_destroy(): void
	{
		$this->assertTrue($this->session->destroy());
	}
}
