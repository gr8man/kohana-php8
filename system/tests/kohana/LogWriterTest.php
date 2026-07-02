<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Log_Writer
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
class TestLog_FormatWriter extends Log_Writer
{
	#[\Override]
	public function write(array $messages): void
	{
	}
}

#[AllowDynamicProperties]
class Kohana_LogWriterTest extends Unittest_TestCase
{
	public function test_to_string_returns_object_hash(): void
	{
		$writer = new TestLog_FormatWriter();
		$result = (string) $writer;
		$this->assertNotEmpty($result);
		$this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $result);
	}

	public function test_format_message_uses_default_format(): void
	{
		$writer = new TestLog_FormatWriter();
		$time = time();
		$message = array(
			'time'    => $time,
			'level'   => LOG_ERR,
			'body'    => 'test error',
			'file'    => '/test.php',
			'line'    => 42,
			'class'   => 'TestClass',
			'function' => 'testFunc',
		);
		$result = $writer->format_message($message);
		$this->assertStringContainsString('ERROR', $result);
		$this->assertStringContainsString('test error', $result);
		$this->assertStringContainsString('/test.php:42', $result);
	}

	public function test_format_message_custom_format(): void
	{
		$writer = new TestLog_FormatWriter();
		$message = array(
			'time'  => time(),
			'level' => LOG_WARNING,
			'body'  => 'custom test',
			'file'  => 'app.php',
			'line'  => 10,
		);
		$result = $writer->format_message($message, 'level: {level} body: {body}');
		$this->assertStringContainsString('WARNING', $result);
		$this->assertStringContainsString('custom test', $result);
	}

	public function test_format_message_with_exception_additional(): void
	{
		$writer = new TestLog_FormatWriter();
		$exception = new Exception('test exception detail');
		$message = array(
			'time'       => time(),
			'level'      => LOG_ERR,
			'body'       => 'error occurred',
			'file'       => 'test.php',
			'line'       => 5,
			'additional' => array('exception' => $exception),
		);
		$result = $writer->format_message($message);
		$this->assertStringContainsString('ERROR', $result);
		$this->assertStringContainsString('error occurred', $result);
		$this->assertStringContainsString('#0', $result);
	}

	public function test_format_message_all_levels(): void
	{
		$writer = new TestLog_FormatWriter();
		$levels = array(
			LOG_EMERG => 'EMERGENCY',
			LOG_ALERT => 'ALERT',
			LOG_CRIT => 'CRITICAL',
			LOG_ERR => 'ERROR',
			LOG_WARNING => 'WARNING',
			LOG_NOTICE => 'NOTICE',
			LOG_INFO => 'INFO',
			LOG_DEBUG => 'DEBUG',
		);
		foreach ($levels as $level => $name) {
			$message = array(
				'time'  => time(),
				'level' => $level,
				'body'  => 'test',
				'file'  => 'f.php',
				'line'  => 1,
			);
			$result = $writer->format_message($message);
			$this->assertStringContainsString($name, $result);
		}
	}
}
