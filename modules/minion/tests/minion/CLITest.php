<?php

declare(strict_types=1);

/**
 * Tests for Minion_CLI::options() method
 *
 * @package    Kohana/Minion
 * @group      kohana
 * @group      kohana.minion
 * @group      kohana.minion.cli
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2024 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Minion_CLITest extends Kohana_Unittest_TestCase
{
	private array $original_argv;

	public function setUp(): void
	{
		parent::setUp();
		$this->original_argv = $_SERVER['argv'] ?? array();
	}

	public function tearDown(): void
	{
		$_SERVER['argv'] = $this->original_argv;
		$_SERVER['argc'] = count($this->original_argv);
		parent::tearDown();
	}

	public function test_options_parses_simple_options(): void
	{
		$_SERVER['argv'] = array('minion', 'task', '--name=John', '--verbose');
		$_SERVER['argc'] = 4;
		$options = Minion_CLI::options();
		$this->assertArrayHasKey('name', $options);
		$this->assertSame('John', $options['name']);
	}

	public function test_options_parses_flag_options(): void
	{
		$_SERVER['argv'] = array('minion', 'task', '--verbose', '--debug');
		$_SERVER['argc'] = 4;
		$options = Minion_CLI::options();
		$this->assertTrue($options['verbose']);
		$this->assertTrue($options['debug']);
	}

	public function test_options_parses_options_with_equals(): void
	{
		$_SERVER['argv'] = array('minion', 'migrate', '--env=production');
		$_SERVER['argc'] = 3;
		$options = Minion_CLI::options();
		$this->assertSame('production', $options['env']);
	}

	public function test_options_returns_positional_as_task(): void
	{
		$_SERVER['argv'] = array('minion', 'db:migrate', '--dry-run');
		$_SERVER['argc'] = 3;
		$result = Minion_CLI::options();
		$this->assertSame('db:migrate', $result[0]);
	}

	public function test_options_with_no_arguments_returns_empty_array(): void
	{
		$_SERVER['argv'] = array('minion');
		$_SERVER['argc'] = 1;
		$result = Minion_CLI::options();
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	public function test_options_parses_multiple_values(): void
	{
		$_SERVER['argv'] = array('minion', 'test', '--ids=1', '--ids=2', '--ids=3');
		$_SERVER['argc'] = 5;
		$options = Minion_CLI::options();
		$this->assertArrayHasKey('ids', $options);
		$this->assertSame('3', $options['ids']);
	}

	public function test_options_parses_boolean_flags(): void
	{
		$_SERVER['argv'] = array('minion', 'clear', '--force', '--no-interaction');
		$_SERVER['argc'] = 4;
		$options = Minion_CLI::options();
		$this->assertTrue($options['force']);
		$this->assertTrue($options['no-interaction']);
	}

	public function test_wait_has_static_method(): void
	{
		$method = new ReflectionMethod(Minion_CLI::class, 'wait');
		$this->assertTrue($method->isStatic());
	}

	public function test_options_reads_from_argv_by_default(): void
	{
		$_SERVER['argv'] = array('minion', 'db:migrate', '--step=1', '--force');
		$_SERVER['argc'] = 4;
		$options = Minion_CLI::options();
		$this->assertArrayHasKey('step', $options);
		$this->assertSame('1', $options['step']);
		$this->assertTrue($options['force']);
	}
}
