<?php

declare(strict_types=1);

/**
 * Tests for Minion_Exception and Minion_Exception_InvalidTask
 *
 * @group kohana
 * @group kohana.minion
 * @group kohana.minion.exception
 *
 * @package    Kohana/Minion
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2024 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Minion_ExceptionTest extends Kohana_Unittest_TestCase
{
	public function test_exception_extends_kohana_exception(): void
	{
		$e = new Minion_Exception('Test message');
		$this->assertInstanceOf(Kohana_Exception::class, $e);
	}

	public function test_exception_stores_message(): void
	{
		$e = new Minion_Exception('Test :param', array(':param' => 'value'));
		$this->assertStringContainsString('value', $e->getMessage());
	}

	public function test_exception_default_code(): void
	{
		$e = new Minion_Exception('Test');
		$this->assertSame(0, $e->getCode());
	}

	public function test_exception_with_code(): void
	{
		$e = new Minion_Exception('Test', array(), 42);
		$this->assertSame(42, $e->getCode());
	}

	public function test_exception_with_previous(): void
	{
		$prev = new RuntimeException('Previous');
		$e = new Minion_Exception('Test', array(), 0, $prev);
		$this->assertSame($prev, $e->getPrevious());
	}

	public function test_format_for_cli_returns_text(): void
	{
		$e = new Minion_Exception('Test error message');
		$formatted = $e->format_for_cli();
		$this->assertIsString($formatted);
		$this->assertStringContainsString('Test error message', $formatted);
	}

	public function test_invalid_task_extends_minion_exception(): void
	{
		$e = new Minion_Exception_InvalidTask('Task not found');
		$this->assertInstanceOf(Minion_Exception::class, $e);
	}

	public function test_invalid_task_format_for_cli(): void
	{
		$e = new Minion_Exception_InvalidTask('Missing task');
		$formatted = $e->format_for_cli();
		$this->assertStringContainsString('ERROR:', $formatted);
		$this->assertStringContainsString('Missing task', $formatted);
	}

	public function test_invalid_task_with_placeholder(): void
	{
		$e = new Minion_Exception_InvalidTask(
			"Task ':task' is not a valid minion task",
			array(':task' => 'Task_Db_Migrate')
		);
		$this->assertStringContainsString('Task_Db_Migrate', $e->getMessage());
	}
}
