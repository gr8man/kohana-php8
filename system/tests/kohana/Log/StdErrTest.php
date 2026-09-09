<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Log_StdErr
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_Log_StdErrTest extends Unittest_TestCase
{
	public function test_write_outputs_to_stderr(): void
	{
		$handle = fopen('php://memory', 'w+');
		$writer = new Log_StdErr($handle);
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_CRIT,
				'body'  => 'stderr test',
				'file'  => 'test.php',
				'line'  => 10,
			),
		));
		rewind($handle);
		$output = stream_get_contents($handle);
		fclose($handle);

		$this->assertStringContainsString('stderr test', $output);
		$this->assertStringContainsString('CRITICAL', $output);
		$this->assertStringContainsString('test.php:10', $output);
	}

	public function test_write_multiple_messages(): void
	{
		$handle = fopen('php://memory', 'w+');
		$writer = new Log_StdErr($handle);
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_ALERT,
				'body'  => 'alert msg',
				'file'  => 'a.php',
				'line'  => 1,
			),
			array(
				'time'  => time(),
				'level' => LOG_EMERG,
				'body'  => 'emerg msg',
				'file'  => 'b.php',
				'line'  => 2,
			),
		));
		rewind($handle);
		$output = stream_get_contents($handle);
		fclose($handle);

		$this->assertStringContainsString('alert msg', $output);
		$this->assertStringContainsString('emerg msg', $output);
		$this->assertStringContainsString('ALERT', $output);
		$this->assertStringContainsString('EMERGENCY', $output);
		$this->assertStringContainsString('a.php:1', $output);
		$this->assertStringContainsString('b.php:2', $output);
	}
}
