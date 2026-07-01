<?php

declare(strict_types=1);

/**
 * Tests for Minion_Task base class methods
 *
 * @group kohana
 * @group kohana.minion
 * @group kohana.minion.task
 *
 * @package    Kohana/Minion
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2024 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Minion_Task_TestHelper extends Minion_Task
{
	public function __construct()
	{
		parent::__construct();
	}

	#[\Override]
	protected function _execute(array $params): void
	{
	}
}

#[AllowDynamicProperties]
class Minion_TaskBaseTest extends Kohana_Unittest_TestCase
{
	protected function createTaskMock(): Minion_Task_TestHelper
	{
		return new Minion_Task_TestHelper();
	}

	public function provider_convert_task_to_class_name(): array
	{
		return array(
			array('Task_Db_Migrate', 'db:migrate'),
			array('Task_Db_Status', 'db:status'),
			array('Task_Help', 'help'),
			array('Task_Migrate', 'migrate'),
			array('Task_A_B_C', 'a:b:c'),
			array('', ''),
			array('', '  '),
		);
	}

	/**
	 * @dataProvider provider_convert_task_to_class_name
	 */
	public function test_convert_task_to_class_name(string $expected, string $task_name): void
	{
		$this->assertSame($expected, Minion_Task::convert_task_to_class_name($task_name));
	}

	public function provider_convert_class_to_task(): array
	{
		return array(
			array('db:migrate', 'Task_Db_Migrate'),
			array('help', 'Task_Help'),
			array('db:status', 'Task_Db_Status'),
		);
	}

	/**
	 * @dataProvider provider_convert_class_to_task
	 */
	public function test_convert_class_to_task(string $expected, string $class): void
	{
		$this->assertSame($expected, Minion_Task::convert_class_to_task($class));
	}

	public function test_convert_class_to_task_with_object(): void
	{
		$task = $this->createTaskMock();
		$result = Minion_Task::convert_class_to_task($task);
		$this->assertIsString($result);
	}

	public function test_factory_returns_help_task_when_no_task_option(): void
	{
		$task = Minion_Task::factory(array());
		$this->assertInstanceOf(Minion_Task::class, $task);
		$this->assertSame('help', (string) $task);
	}

	public function test_factory_throws_on_invalid_task(): void
	{
		$this->expectException(Minion_Exception_InvalidTask::class);
		Minion_Task::factory(array('task' => 'nonexistent_task_xyz'));
	}

	public function test_get_options_returns_array(): void
	{
		$task = $this->createTaskMock();
		$this->assertIsArray($task->get_options());
	}

	public function test_set_options_returns_self(): void
	{
		$task = $this->createTaskMock();
		$result = $task->set_options(array('test' => 'value'));
		$this->assertSame($task, $result);
	}

	public function test_set_options_stores_values(): void
	{
		$task = $this->createTaskMock();
		$task->set_options(array('foo' => 'bar'));
		$options = $task->get_options();
		$this->assertSame('bar', $options['foo']);
	}

	public function test_get_accepted_options_returns_array(): void
	{
		$task = $this->createTaskMock();
		$this->assertIsArray($task->get_accepted_options());
	}

	public function test_to_string_returns_task_name(): void
	{
		$task = $this->createTaskMock();
		$str = (string) $task;
		$this->assertIsString($str);
	}

	public function test_factory_with_positional_task(): void
	{
		$task = Minion_Task::factory(array(0 => 'help'));
		$this->assertInstanceOf(Minion_Task::class, $task);
		$this->assertSame('help', (string) $task);
	}
}
