<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Log_Syslog
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
class Kohana_Log_SyslogTest extends Unittest_TestCase
{
	public function test_construct(): void
	{
		$writer = new Log_Syslog('KohanaPHP_Test');
		$this->assertInstanceOf(Log_Syslog::class, $writer);
	}

	public function test_write(): void
	{
		$writer = new Log_Syslog('KohanaPHP_Test');
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_INFO,
				'body'  => 'syslog test message',
				'file'  => 'test.php',
				'line'  => 42,
			),
		));
		$this->assertTrue(true);
	}

	public function test_write_multiple_messages(): void
	{
		$writer = new Log_Syslog('KohanaPHP_Test');
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_WARNING,
				'body'  => 'warning message',
				'file'  => 'a.php',
				'line'  => 1,
			),
			array(
				'time'  => time(),
				'level' => LOG_ERR,
				'body'  => 'error message',
				'file'  => 'b.php',
				'line'  => 2,
			),
		));
		$this->assertTrue(true);
	}

	public function test_write_with_exception(): void
	{
		$writer = new Log_Syslog('KohanaPHP_Test');
		$exception = new Exception('test exception detail');
		$writer->write(array(
			array(
				'time'       => time(),
				'level'      => LOG_CRIT,
				'body'       => 'critical error',
				'file'       => 'test.php',
				'line'       => 5,
				'additional' => array('exception' => $exception),
			),
		));
		$this->assertTrue(true);
	}
}
