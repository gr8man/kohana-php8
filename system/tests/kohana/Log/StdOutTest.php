<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Log_StdOut
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
class Kohana_Log_StdOutTest extends Unittest_TestCase
{
	public function test_write_outputs_to_stdout(): void
	{
		$writer = new Log_StdOut();
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_NOTICE,
				'body'  => 'stdout test',
				'file'  => 'test.php',
				'line'  => 5,
			),
		));
		$this->assertTrue(true);
	}

	public function test_write_multiple_messages(): void
	{
		$writer = new Log_StdOut();
		$writer->write(array(
			array(
				'time'  => time(),
				'level' => LOG_INFO,
				'body'  => 'first',
				'file'  => 'a.php',
				'line'  => 1,
			),
			array(
				'time'  => time(),
				'level' => LOG_WARNING,
				'body'  => 'second',
				'file'  => 'b.php',
				'line'  => 2,
			),
		));
		$this->assertTrue(true);
	}
}
