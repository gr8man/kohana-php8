<?php

declare(strict_types=1);

/**
 * Tests for Unittest_Helpers
 *
 * @group kohana
 * @group kohana.unittest
 * @group kohana.unittest.helpers
 *
 * @package    Kohana/Unittest
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_Unittest_HelpersTest extends Unittest_TestCase
{
	public function test_dir_separator_converts_forward_slashes(): void
	{
		$result = Unittest_Helpers::dir_separator('path/to/file');
		$this->assertSame('path' . DIRECTORY_SEPARATOR . 'to' . DIRECTORY_SEPARATOR . 'file', $result);
	}

	public function test_dir_separator_does_not_change_valid_path(): void
	{
		$path = 'path' . DIRECTORY_SEPARATOR . 'file';
		$result = Unittest_Helpers::dir_separator($path);
		$this->assertSame($path, $result);
	}

	public function test_dir_separator_empty_string(): void
	{
		$this->assertSame('', Unittest_Helpers::dir_separator(''));
	}

	public function test_has_internet_returns_bool(): void
	{
		$result = Unittest_Helpers::has_internet();
		$this->assertIsBool($result);
	}

	public function test_set_environment_backup_and_restore(): void
	{
		$helpers = new Unittest_Helpers();

		$helpers->set_environment(array('TEST_ENV_VAR' => 'test_value'));
		$this->assertSame('test_value', $_SERVER['TEST_ENV_VAR']);

		$helpers->restore_environment();
		$this->assertSame('', $_SERVER['TEST_ENV_VAR'] ?? '');
	}

	public function test_set_environment_empty_returns_false(): void
	{
		$helpers = new Unittest_Helpers();
		$result = $helpers->set_environment(array());
		$this->assertFalse($result);
	}

	public function test_clean_cache_dir_runs_without_error(): void
	{
		Unittest_Helpers::clean_cache_dir();
		$this->assertTrue(true);
	}
}
