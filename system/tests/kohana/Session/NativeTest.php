<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Session_Native
 *
 * @group kohana
 * @group kohana.session
 * @group kohana.session.native
 * @package    Kohana
 * @category   Tests
 */
class Kohana_Session_NativeTest extends Unittest_TestCase
{
	protected Session_Native $session;

	public function setUp(): void
	{
		parent::setUp();
		$this->session = new Session_Native(array('name' => 'test_session'));
	}

	public function test_get_set_and_delete(): void
	{
		$this->session->set('key', 'value');
		$this->assertSame('value', $this->session->get('key'));

		$this->session->delete('key');
		$this->assertNull($this->session->get('key'));
		$this->assertSame('default', $this->session->get('key', 'default'));
	}

	public function test_get_once(): void
	{
		$this->session->set('flash', 'once_only');
		$val = $this->session->get_once('flash');
		$this->assertSame('once_only', $val);
		$this->assertNull($this->session->get('flash'));
	}

	public function test_bind(): void
	{
		$var = 'initial';
		$this->session->bind('bound_key', $var);
		$this->assertSame('initial', $this->session->get('bound_key'));

		$var = 'updated';
		$this->assertSame('updated', $this->session->get('bound_key'));
	}

	public function test_as_array(): void
	{
		$this->session->set('a', 1);
		$this->session->set('b', 2);
		$arr = $this->session->as_array();
		$this->assertIsArray($arr);
		$this->assertSame(1, $arr['a']);
		$this->assertSame(2, $arr['b']);
	}

	public function test_native_lifecycle_methods(): void
	{
		$id = $this->session->id();
		$this->assertTrue(is_string($id) || $id === null);

		$ref_regen = new ReflectionMethod($this->session, '_regenerate');
		$ref_regen->setAccessible(true);
		$regen_id = $ref_regen->invoke($this->session);
		$this->assertTrue(is_string($regen_id) || $regen_id === false);

		$ref_write = new ReflectionMethod($this->session, '_write');
		$ref_write->setAccessible(true);
		$this->assertTrue($ref_write->invoke($this->session));

		$ref_restart = new ReflectionMethod($this->session, '_restart');
		$ref_restart->setAccessible(true);
		$ref_restart->invoke($this->session);

		$ref_destroy = new ReflectionMethod($this->session, '_destroy');
		$ref_destroy->setAccessible(true);
		$this->assertTrue($ref_destroy->invoke($this->session));
	}
}
