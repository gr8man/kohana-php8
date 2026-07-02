<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Validation_Exception
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.validation
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ValidationExceptionTest extends Unittest_TestCase
{
	public function test_constructor_stores_validation_object(): void
	{
		$post = Validation::factory(array('name' => 'test'));
		$exception = new Validation_Exception($post);
		$this->assertSame($post, $exception->array);
	}

	public function test_default_message(): void
	{
		$post = Validation::factory(array());
		$exception = new Validation_Exception($post);
		$this->assertSame('Failed to validate array', $exception->getMessage());
	}

	public function test_custom_message(): void
	{
		$post = Validation::factory(array());
		$exception = new Validation_Exception($post, 'Custom error');
		$this->assertSame('Custom error', $exception->getMessage());
	}

	public function test_is_kohana_exception(): void
	{
		$post = Validation::factory(array());
		$exception = new Validation_Exception($post);
		$this->assertInstanceOf(Kohana_Exception::class, $exception);
	}

	public function test_can_set_code_and_previous(): void
	{
		$post = Validation::factory(array());
		$previous = new Exception('prev');
		$exception = new Validation_Exception($post, 'msg', null, 42, $previous);
		$this->assertSame(42, $exception->getCode());
		$this->assertSame($previous, $exception->getPrevious());
	}
}
