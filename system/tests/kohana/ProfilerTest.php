<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Profiler
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.profiler
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ProfilerTest extends Unittest_TestCase
{
	// @codingStandardsIgnoreStart
	public function tearDown(): void
	// @codingStandardsIgnoreEnd
	{
		parent::tearDown();
		self::_reset_marks();
	}

	private static function _reset_marks(): void
	{
		$ref = new ReflectionClass(Profiler::class);
		$prop = $ref->getProperty('_marks');
		$prop->setAccessible(true);
		$prop->setValue(array());
	}

	public function test_start_returns_token(): void
	{
		$token = Profiler::start('test_group', 'test_name');
		$this->assertNotEmpty($token);
		$this->assertStringStartsWith('kp/', $token);
	}

	public function test_start_and_stop(): void
	{
		$token = Profiler::start('test_group', 'test_name');
		Profiler::stop($token);

		$result = Profiler::total($token);
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertIsFloat($result[0]);
		$this->assertIsInt($result[1]);
	}

	public function test_total_without_stop_auto_stops(): void
	{
		$token = Profiler::start('auto_stop', 'no_stop');
		$result = Profiler::total($token);
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
	}

	public function test_delete_removes_token(): void
	{
		$token = Profiler::start('delete_group', 'delete_name');
		Profiler::delete($token);

		$groups = Profiler::groups();
		$this->assertArrayNotHasKey('delete_group', $groups);
	}

	public function test_groups_returns_grouped_tokens(): void
	{
		$token1 = Profiler::start('group_a', 'method_1');
		Profiler::stop($token1);
		$token2 = Profiler::start('group_a', 'method_2');
		Profiler::stop($token2);
		$token3 = Profiler::start('group_b', 'method_1');
		Profiler::stop($token3);

		$groups = Profiler::groups();
		$this->assertArrayHasKey('group_a', $groups);
		$this->assertArrayHasKey('group_b', $groups);
		$this->assertArrayHasKey('method_1', $groups['group_a']);
		$this->assertArrayHasKey('method_2', $groups['group_a']);
		$this->assertArrayHasKey('method_1', $groups['group_b']);
		$this->assertCount(1, $groups['group_a']['method_1']);
		$this->assertCount(1, $groups['group_a']['method_2']);
		$this->assertCount(1, $groups['group_b']['method_1']);
	}

	public function test_stats_returns_calculations(): void
	{
		$token = Profiler::start('stats_group', 'stats_method');
		Profiler::stop($token);

		$stats = Profiler::stats(array($token));
		$this->assertArrayHasKey('min', $stats);
		$this->assertArrayHasKey('max', $stats);
		$this->assertArrayHasKey('total', $stats);
		$this->assertArrayHasKey('average', $stats);
		$this->assertArrayHasKey('time', $stats['total']);
		$this->assertArrayHasKey('memory', $stats['total']);
	}

	public function test_stats_multiple_tokens(): void
	{
		$a = Profiler::start('multi', 'a');
		Profiler::stop($a);
		$b = Profiler::start('multi', 'b');
		Profiler::stop($b);

		$stats = Profiler::stats(array($a, $b));
		$this->assertIsNumeric($stats['min']['time']);
		$this->assertIsNumeric($stats['max']['time']);
		$this->assertIsNumeric($stats['total']['time']);
		$this->assertIsNumeric($stats['average']['time']);
	}

	public function test_group_stats_returns_group_calculations(): void
	{
		$token = Profiler::start('gs_group', 'gs_name');
		Profiler::stop($token);

		$gstats = Profiler::group_stats();
		$this->assertArrayHasKey('gs_group', $gstats);
		$this->assertArrayHasKey('total', $gstats['gs_group']);
		$this->assertArrayHasKey('min', $gstats['gs_group']);
		$this->assertArrayHasKey('max', $gstats['gs_group']);
		$this->assertArrayHasKey('average', $gstats['gs_group']);
	}

	public function test_group_stats_with_specific_group(): void
	{
		$token = Profiler::start('specific_gs', 'test_method');
		Profiler::stop($token);

		$gstats = Profiler::group_stats('specific_gs');
		$this->assertArrayHasKey('specific_gs', $gstats);
		$this->assertArrayNotHasKey('nonexistent', $gstats);
	}

	public function test_group_stats_with_multiple_groups(): void
	{
		$a = Profiler::start('multi_gs', 'a');
		Profiler::stop($a);
		$b = Profiler::start('multi_gs', 'b');
		Profiler::stop($b);

		$gstats = Profiler::group_stats(array('multi_gs'));
		$this->assertArrayHasKey('multi_gs', $gstats);
		$this->assertCount(2, $gstats['multi_gs']['total']);
	}
}
