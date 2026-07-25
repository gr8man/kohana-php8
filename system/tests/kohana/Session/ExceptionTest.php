<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Session_Exception
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.session
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_Session_ExceptionTest extends Unittest_TestCase
{
	public function test_extends_kohana_exception(): void
	{
		$exception = new Session_Exception('test');
		$this->assertInstanceOf(Kohana_Exception::class, $exception);
	}

	public function test_construct_with_message(): void
	{
		$exception = new Session_Exception('session error');
		$this->assertSame('session error', $exception->getMessage());
	}

	public function test_construct_with_code(): void
	{
		$exception = new Session_Exception('corrupt', array(), Session_Exception::SESSION_CORRUPT);
		$this->assertSame(Session_Exception::SESSION_CORRUPT, $exception->getCode());
	}

	public function test_session_corrupt_constant_defined(): void
	{
		$this->assertEquals(1, Session_Exception::SESSION_CORRUPT);
	}

	public function test_construct_with_variables(): void
	{
		$exception = new Session_Exception(':var error', array(':var' => 'session'));
		$this->assertStringContainsString('session', $exception->getMessage());
	}
}
