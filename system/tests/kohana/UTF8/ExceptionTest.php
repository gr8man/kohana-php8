<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests UTF8_Exception
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.utf8
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_UTF8_ExceptionTest extends Unittest_TestCase
{
	public function test_extends_kohana_exception(): void
	{
		$exception = new UTF8_Exception('test');
		$this->assertInstanceOf(Kohana_Exception::class, $exception);
	}

	public function test_construct_with_message(): void
	{
		$exception = new UTF8_Exception('utf8 error');
		$this->assertSame('utf8 error', $exception->getMessage());
	}

	public function test_construct_with_variables(): void
	{
		$exception = new UTF8_Exception(':char is invalid', array(':char' => "\xfe"));
		$this->assertStringContainsString("\xfe", $exception->getMessage());
	}

	public function test_construct_with_code(): void
	{
		$exception = new UTF8_Exception('error', array(), 10);
		$this->assertSame(10, $exception->getCode());
	}
}
