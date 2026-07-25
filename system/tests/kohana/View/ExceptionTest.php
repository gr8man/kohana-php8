<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests View_Exception
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.view
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_View_ExceptionTest extends Unittest_TestCase
{
	public function test_extends_kohana_exception(): void
	{
		$exception = new View_Exception('test');
		$this->assertInstanceOf(Kohana_Exception::class, $exception);
	}

	public function test_construct_with_message(): void
	{
		$exception = new View_Exception('view error');
		$this->assertSame('view error', $exception->getMessage());
	}

	public function test_construct_with_variables(): void
	{
		$exception = new View_Exception(':view not found', array(':view' => 'missing'));
		$this->assertStringContainsString('missing', $exception->getMessage());
	}

	public function test_construct_with_code(): void
	{
		$exception = new View_Exception('error', array(), 99);
		$this->assertSame(99, $exception->getCode());
	}
}
