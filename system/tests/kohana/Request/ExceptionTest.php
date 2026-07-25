<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Request_Exception
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.request
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_Request_ExceptionTest extends Unittest_TestCase
{
	public function test_extends_kohana_exception(): void
	{
		$exception = new Request_Exception('test');
		$this->assertInstanceOf(Kohana_Exception::class, $exception);
	}

	public function test_construct_with_message(): void
	{
		$exception = new Request_Exception('request error');
		$this->assertSame('request error', $exception->getMessage());
	}

	public function test_construct_with_variables(): void
	{
		$exception = new Request_Exception(':uri not found', array(':uri' => '/test'));
		$this->assertStringContainsString('/test', $exception->getMessage());
	}

	public function test_construct_with_code(): void
	{
		$exception = new Request_Exception('error', array(), 42);
		$this->assertSame(42, $exception->getCode());
	}
}
