<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Log_File
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_Log_FileTest extends Unittest_TestCase
{
	private string $_temp_dir;

	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		$this->_temp_dir = sys_get_temp_dir() . '/kohana-log-file-test-' . uniqid();
		mkdir($this->_temp_dir, 0777, true);
	}

	// @codingStandardsIgnoreStart
	public function tearDown(): void
	// @codingStandardsIgnoreEnd
	{
		self::_rrmdir($this->_temp_dir);
		parent::tearDown();
	}

	private static function _rrmdir(string $dir): void
	{
		if (! is_dir($dir)) {
			return;
		}
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($files as $fileinfo) {
			$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
			$todo($fileinfo->getRealPath());
		}
		rmdir($dir);
	}

	public function test_construct_with_valid_directory(): void
	{
		$writer = new Log_File($this->_temp_dir);
		$this->assertInstanceOf(Log_File::class, $writer);
	}

	public function test_construct_with_invalid_directory(): void
	{
		$this->expectException(Kohana_Exception::class);
		new Log_File('/nonexistent/path/' . uniqid());
	}

	public function test_write_creates_log_file(): void
	{
		$writer = new Log_File($this->_temp_dir);
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_INFO,
				'body'  => 'test message',
				'file'  => 'test.php',
				'line'  => 42,
			),
		));

		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$log_file = $this->_temp_dir . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $day . EXT;

		$this->assertFileExists($log_file);
		$contents = file_get_contents($log_file);
		$this->assertStringContainsString('test message', $contents);
		$this->assertStringContainsString('INFO', $contents);
	}

	public function test_write_appends_multiple_messages(): void
	{
		$writer = new Log_File($this->_temp_dir);
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_ERR,
				'body'  => 'first error',
				'file'  => 'test.php',
				'line'  => 1,
			),
			array(
				'time'  => time(),
				'level' => LOG_DEBUG,
				'body'  => 'second debug',
				'file'  => 'test.php',
				'line'  => 2,
			),
		));

		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$log_file = $this->_temp_dir . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $day . EXT;

		$contents = file_get_contents($log_file);
		$this->assertStringContainsString('first error', $contents);
		$this->assertStringContainsString('second debug', $contents);
		$this->assertStringContainsString('ERROR', $contents);
		$this->assertStringContainsString('DEBUG', $contents);
	}
}
