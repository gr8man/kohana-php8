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
		$writer = new Log_StdErr();
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_CRIT,
				'body'  => 'stderr test',
				'file'  => 'test.php',
				'line'  => 10,
			),
		));
		$this->assertTrue(true);
	}

	public function test_write_multiple_messages(): void
	{
		$writer = new Log_StdErr();
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
		$this->assertTrue(true);
	}
}
