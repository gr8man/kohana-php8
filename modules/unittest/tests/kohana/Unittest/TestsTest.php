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

	public function test_blacklist_and_whitelist(): void
	{
		$suite = new Unittest_TestSuite('suite_test');
		Unittest_Tests::blacklist(array(APPPATH.'classes', APPPATH.'bootstrap.php'), $suite);
		Unittest_Tests::whitelist(array(APPPATH.'classes'), $suite);
		$this->assertTrue(true);
	}

	public function test_add_tests(): void
	{
		$suite = new Unittest_TestSuite('suite_add_tests');
		$files = array(
			'test_sub' => array(
				'test1' => APPPATH.'classes'.DIRECTORY_SEPARATOR.'test.php',
			),
		);
		Unittest_Tests::addTests($suite, $files);
		$this->assertTrue(true);
	}

	public function test_get_config_whitelist(): void
	{
		$ref = new ReflectionMethod(Unittest_Tests::class, 'get_config_whitelist');
		$dirs = $ref->invoke(null);
		$this->assertIsArray($dirs);
	}
}
