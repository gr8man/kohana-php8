<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Unittest_TestCase helper methods
 *
 * @group kohana
 * @group kohana.unittest
 * @package    Kohana/Unittest
 * @category   Tests
 */
class Kohana_Unittest_TestCaseTest extends Unittest_TestCase
{
	public function test_assert_attribute_methods(): void
	{
		$obj = new stdClass();
		$obj->name = 'test';
		$obj->count = 5;
		$obj->items = ['apple', 'banana'];

		$this->assertAttributeSame('test', 'name', $obj);
		$this->assertAttributeEquals(5, 'count', $obj);
		$this->assertAttributeNotSame('other', 'name', $obj);
		$this->assertAttributeContains('apple', 'items', $obj);
		$this->assertAttributeNotContains('orange', 'items', $obj);

		$this->assertContains('test', 'a test string');
		$this->assertNotContains('foo', 'a test string');
	}

	public function test_assert_internal_type(): void
	{
		$this->assertInternalType('string', 'hello');
		$this->assertInternalType('int', 123);
		$this->assertInternalType('float', 3.14);
		$this->assertInternalType('bool', true);
		$this->assertInternalType('array', [1, 2]);
		$this->assertInternalType('object', new stdClass());
		$this->assertInternalType('null', null);
		$this->assertInternalType('callable', fn(): true => true);
		$this->assertInternalType('iterable', [1, 2]);
	}

	public function test_environment_helpers(): void
	{
		$this->setEnvironment([
			'_SERVER' => ['TEST_ENV_VAR' => 'test_val'],
		]);
		$this->assertSame('test_val', $_SERVER['TEST_ENV_VAR']);
	}
}
