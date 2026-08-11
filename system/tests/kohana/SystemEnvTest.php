<?php

declare(strict_types=1);

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana system environment and installer checks
 *
 * @group kohana
 * @group kohana.core
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_SystemEnvTest extends Unittest_TestCase
{
	/**
	 * @test
	 */
	public function test_install_script_passes_environment_checks(): void
	{
		$_SERVER['REQUEST_URI'] = '/';
		$_SERVER['PHP_SELF'] = '/index.php';

		ob_start();
		include SYSPATH . '../install.php';
		$output = ob_get_clean();

		$this->assertStringContainsString('Your environment passed all requirements', $output);
	}

	/**
	 * @test
	 */
	public function test_system_directories_exist(): void
	{
		$this->assertDirectoryExists(SYSPATH);
		$this->assertDirectoryExists(APPPATH);
		$this->assertDirectoryExists(MODPATH);
	}

	/**
	 * @test
	 */
	public function test_cache_and_logs_directories_are_writable(): void
	{
		$cache_dir = APPPATH . 'cache';
		$logs_dir = APPPATH . 'logs';

		if (! is_dir($cache_dir)) {
			mkdir($cache_dir, 0777, true);
		}
		if (! is_dir($logs_dir)) {
			mkdir($logs_dir, 0777, true);
		}

		$this->assertTrue(is_writable($cache_dir));
		$this->assertTrue(is_writable($logs_dir));
	}
}
