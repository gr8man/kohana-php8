<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Unittest_Tests class
 *
 * @group kohana
 * @group kohana.unittest
 * @package    Kohana/Unittest
 * @category   Tests
 */
class Kohana_Unittest_TestsTest extends Unittest_TestCase
{
	public function test_autoload(): void
	{
		Unittest_Tests::autoload('NonExistent_TestClass_FooBar');
		$this->assertTrue(true);
	}

	public function test_configure_environment(): void
	{
		Unittest_Tests::configure_environment(false, false);
		$this->assertTrue(true);
	}
}
