<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Unittest_TestSuite
 *
 * @group kohana
 * @group kohana.unittest
 * @package    Kohana/Unittest
 * @category   Tests
 */
class Kohana_Unittest_TestSuiteTest extends Unittest_TestCase
{
	public function test_filter_queues(): void
	{
		$suite = new Unittest_TestSuite('test_suite');
		$suite->addFileToBlacklist('some/file.php');
		$suite->addDirectoryToBlacklist('some/dir/');
		$suite->addFileToWhitelist('some/other/file.php');

		$ref = new ReflectionProperty($suite, '_filter_calls');
		$calls = $ref->getValue($suite);

		$this->assertContains('some/file.php', $calls['addFileToBlacklist']);
		$this->assertContains('some/dir/', $calls['addDirectoryToBlacklist']);
		$this->assertContains('some/other/file.php', $calls['addFileToWhitelist']);
	}
}
