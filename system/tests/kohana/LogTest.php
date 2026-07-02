<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Logging API
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class TestLog_ArrayWriter extends Log_Writer
{
	public array $written = array();

	#[\Override]
	public function write(array $messages): void
	{
		$this->written = array_merge($this->written, $messages);
	}
}

#[AllowDynamicProperties]
class Kohana_LogTest extends Unittest_TestCase
{
	/**
	 * Tests that when a new logger is created the list of messages is initially
	 * empty
	 *
	 * @test
	 * @covers Log
	 */
	public function test_messages_is_initially_empty()
	{
		$logger = new Log();

		$this->assertAttributeSame(array(), '_messages', $logger);
	}

	/**
	 * Tests that when a new logger is created the list of writers is initially
	 * empty
	 *
	 * @test
	 * @covers Log
	 */
	public function test_writers_is_initially_empty()
	{
		$logger = new Log();

		$this->assertAttributeSame(array(), '_writers', $logger);
	}

	/**
	 * Test that attaching a log writer using an array of levels adds it to the array of log writers
	 *
	 * @TODO Is this test too specific?
	 *
	 * @test
	 * @covers Log::attach
	 */
	public function test_attach_attaches_log_writer_and_returns_this()
	{
		$logger = new Log();
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$this->assertSame($logger, $logger->attach($writer));

		$this->assertAttributeSame(
			array(spl_object_hash($writer) => array('object' => $writer, 'levels' => array())),
			'_writers',
			$logger
		);
	}

	/**
	 * Test that attaching a log writer using a min/max level adds it to the array of log writers
	 *
	 * @TODO Is this test too specific?
	 *
	 * @test
	 * @covers Log::attach
	 */
	public function test_attach_attaches_log_writer_min_max_and_returns_this()
	{
		$logger = new Log();
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$this->assertSame($logger, $logger->attach($writer, Log::NOTICE, Log::CRITICAL));

		$this->assertAttributeSame(
			array(spl_object_hash($writer) => array('object' => $writer, 'levels' => array(Log::CRITICAL, Log::ERROR, Log::WARNING, Log::NOTICE))),
			'_writers',
			$logger
		);
	}

	/**
	 * When we call detach() we expect the specified log writer to be removed
	 *
	 * @test
	 * @covers Log::detach
	 */
	public function test_detach_removes_log_writer_and_returns_this()
	{
		$logger = new Log();
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$logger->attach($writer);

		$this->assertSame($logger, $logger->detach($writer));

		$this->assertAttributeSame(array(), '_writers', $logger);
	}

	public function test_add_stores_message(): void
	{
		$logger = new Log();
		$logger->add(Log::ERROR, 'test message');
		$messages = (new ReflectionClass($logger))->getProperty('_messages')->getValue($logger);
		$this->assertCount(1, $messages);
		$this->assertSame('test message', $messages[0]['body']);
		$this->assertSame(Log::ERROR, $messages[0]['level']);
	}

	public function test_add_with_values_replaces_placeholders(): void
	{
		$logger = new Log();
		$logger->add(Log::INFO, 'Hello :name', array(':name' => 'World'));
		$messages = (new ReflectionClass($logger))->getProperty('_messages')->getValue($logger);
		$this->assertSame('Hello World', $messages[0]['body']);
	}

	public function test_write_sends_to_writer(): void
	{
		$logger = new Log();
		$writer = new TestLog_ArrayWriter();
		$logger->attach($writer);
		$logger->add(Log::DEBUG, 'write test');
		$logger->write();
		$this->assertCount(1, $writer->written);
		$this->assertSame('write test', $writer->written[0]['body']);
	}

	public function test_write_with_level_filter(): void
	{
		$logger = new Log();
		$writer = new TestLog_ArrayWriter();
		$logger->attach($writer, array(Log::ERROR));
		$logger->add(Log::DEBUG, 'should not appear');
		$logger->add(Log::ERROR, 'should appear');
		$logger->write();
		$this->assertCount(1, $writer->written);
		$this->assertSame('should appear', $writer->written[0]['body']);
	}

	public function test_add_with_exception_trace(): void
	{
		$logger = new Log();
		$exception = new Exception('test exception');
		$logger->add(Log::ERROR, 'error occurred', null, array('exception' => $exception));
		$messages = (new ReflectionClass($logger))->getProperty('_messages')->getValue($logger);
		$this->assertSame('error occurred', $messages[0]['body']);
		$this->assertArrayHasKey('trace', $messages[0]);
	}

	public function test_write_empty_does_nothing(): void
	{
		$logger = new Log();
		$writer = new TestLog_ArrayWriter();
		$logger->attach($writer);
		$logger->write();
		$this->assertCount(0, $writer->written);
	}

	public function test_write_on_add(): void
	{
		$logger = new Log();
		$writer = new TestLog_ArrayWriter();
		$logger->attach($writer);
		Log::$write_on_add = true;
		$logger->add(Log::NOTICE, 'immediate write');
		$this->assertCount(1, $writer->written);
		Log::$write_on_add = false;
	}

	public function test_instance_returns_singleton(): void
	{
		$instance1 = Log::instance();
		$instance2 = Log::instance();
		$this->assertSame($instance1, $instance2);
	}
}
