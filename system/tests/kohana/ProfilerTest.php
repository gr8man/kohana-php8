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

	/**
	 * Test that groups() returns an empty array when no marks exist
	 *
	 * @test
	 * @covers Profiler::groups
	 */
	public function test_groups_empty_when_no_marks(): void
	{
		$groups = Profiler::groups();
		$this->assertSame(array(), $groups);
	}

	/**
	 * Test that group names are stored in lowercase
	 *
	 * @test
	 * @covers Profiler::start
	 * @covers Profiler::groups
	 */
	public function test_group_names_are_lowercased(): void
	{
		$token = Profiler::start('UPPERCASE_GROUP', 'test_method');
		Profiler::stop($token);

		$groups = Profiler::groups();
		$this->assertArrayHasKey('uppercase_group', $groups);
		$this->assertArrayNotHasKey('UPPERCASE_GROUP', $groups);
	}

	/**
	 * Test that stats with a single token yields min = max = total = average
	 *
	 * @test
	 * @covers Profiler::stats
	 */
	public function test_stats_single_token_values(): void
	{
		$token = Profiler::start('solo', 'test');
		Profiler::stop($token);

		$stats = Profiler::stats(array($token));
		$this->assertSame($stats['min']['time'], $stats['max']['time']);
		$this->assertSame($stats['min']['time'], $stats['total']['time']);
		$this->assertSame($stats['min']['memory'], $stats['max']['memory']);
		$this->assertSame($stats['min']['memory'], $stats['total']['memory']);
	}

	/**
	 * Test that stats works correctly with multiple tokens having different values
	 *
	 * @test
	 * @covers Profiler::stats
	 */
	public function test_stats_multiple_tokens_edge_cases(): void
	{
		$t1 = Profiler::start('multi_edge', 'a');
		Profiler::stop($t1);
		$t2 = Profiler::start('multi_edge', 'b');
		Profiler::stop($t2);

		$stats = Profiler::stats(array($t1, $t2));
		$this->assertIsFloat($stats['min']['time']);
		$this->assertIsFloat($stats['max']['time']);
		$this->assertIsFloat($stats['total']['time']);
		$this->assertIsFloat($stats['average']['time']);
		$this->assertIsInt($stats['min']['memory']);
		$this->assertIsInt($stats['max']['memory']);
		$this->assertIsInt($stats['total']['memory']);
		$this->assertGreaterThanOrEqual($stats['min']['time'], $stats['max']['time']);
		$this->assertGreaterThanOrEqual($stats['min']['memory'], $stats['max']['memory']);
	}

	/**
	 * Test that total returns correct values for a stopped benchmark
	 *
	 * @test
	 * @covers Profiler::total
	 */
	public function test_total_with_stopped_benchmark(): void
	{
		$token = Profiler::start('total_stopped', 'test');
		Profiler::stop($token);

		$result = Profiler::total($token);
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertIsFloat($result[0]);
		$this->assertIsInt($result[1]);
		$this->assertGreaterThanOrEqual(0, $result[0]);
		$this->assertGreaterThanOrEqual(0, $result[1]);
	}

	/**
	 * Test that delete actually removes the token from internal _marks via reflection
	 *
	 * @test
	 * @covers Profiler::delete
	 */
	public function test_delete_removes_from_internal_marks(): void
	{
		$token = Profiler::start('internal_del', 'test');
		Profiler::delete($token);

		$ref = new ReflectionClass(Profiler::class);
		$prop = $ref->getProperty('_marks');
		$prop->setAccessible(true);
		$marks = $prop->getValue();

		$this->assertArrayNotHasKey($token, $marks);
	}

	/**
	 * Test that application() returns the correct array structure
	 *
	 * @test
	 * @covers Profiler::application
	 */
	public function test_application_returns_array_structure(): void
	{
		$result = Profiler::application();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('min', $result);
		$this->assertArrayHasKey('max', $result);
		$this->assertArrayHasKey('total', $result);
		$this->assertArrayHasKey('count', $result);
		$this->assertArrayHasKey('average', $result);
		$this->assertArrayHasKey('current', $result);
		$this->assertArrayHasKey('time', $result['current']);
		$this->assertArrayHasKey('memory', $result['current']);
		$this->assertIsInt($result['count']);
		$this->assertGreaterThanOrEqual(1, $result['count']);
	}

	/**
	 * Test that application() resets stats when count exceeds rollover
	 *
	 * @test
	 * @covers Profiler::application
	 */
	public function test_application_rollover_resets_stats(): void
	{
		$orig_rollover = Profiler::$rollover;
		Profiler::$rollover = 0;

		$result = Profiler::application();
		$this->assertEquals(1, $result['count']);

		Profiler::$rollover = $orig_rollover;
	}

	/**
	 * Test that group_stats with null explicitly returns all groups
	 *
	 * @test
	 * @covers Profiler::group_stats
	 */
	public function test_group_stats_null_explicitly(): void
	{
		$token = Profiler::start('null_gs', 'method');
		Profiler::stop($token);

		$gstats = Profiler::group_stats(null);
		$this->assertArrayHasKey('null_gs', $gstats);
	}

	/**
	 * Test that group_stats with a non-existent group returns an empty array
	 *
	 * @test
	 * @covers Profiler::group_stats
	 */
	public function test_group_stats_non_existent_group(): void
	{
		$token = Profiler::start('real_gs', 'method');
		Profiler::stop($token);

		$gstats = Profiler::group_stats('nonexistent');
		$this->assertSame(array(), $gstats);
	}

	/**
	 * Test that group_stats aggregation produces correct structure with multiple subgroups
	 *
	 * @test
	 * @covers Profiler::group_stats
	 */
	public function test_group_stats_aggregation_structure(): void
	{
		$t1 = Profiler::start('agg_gs', 'method_a');
		Profiler::stop($t1);
		$t2 = Profiler::start('agg_gs', 'method_b');
		Profiler::stop($t2);

		$gstats = Profiler::group_stats('agg_gs');
		$this->assertArrayHasKey('agg_gs', $gstats);
		$this->assertArrayHasKey('min', $gstats['agg_gs']);
		$this->assertArrayHasKey('max', $gstats['agg_gs']);
		$this->assertArrayHasKey('total', $gstats['agg_gs']);
		$this->assertArrayHasKey('average', $gstats['agg_gs']);
		$this->assertArrayHasKey('time', $gstats['agg_gs']['total']);
		$this->assertArrayHasKey('memory', $gstats['agg_gs']['total']);
		$this->assertIsFloat($gstats['agg_gs']['total']['time']);
		$this->assertIsInt($gstats['agg_gs']['total']['memory']);
		$this->assertIsFloat($gstats['agg_gs']['average']['time']);
	}

	/**
	 * Test that group_stats with no marks returns an empty array
	 *
	 * @test
	 * @covers Profiler::group_stats
	 */
	public function test_group_stats_no_marks(): void
	{
		$gstats = Profiler::group_stats();
		$this->assertSame(array(), $gstats);
	}

	/**
	 * Test that group_stats with multiple different groups returns stats for each
	 *
	 * @test
	 * @covers Profiler::group_stats
	 */
	public function test_group_stats_multiple_different_groups(): void
	{
		$t1 = Profiler::start('group_one', 'method_a');
		Profiler::stop($t1);
		$t2 = Profiler::start('group_two', 'method_b');
		Profiler::stop($t2);

		$gstats = Profiler::group_stats(array('group_one', 'group_two'));
		$this->assertArrayHasKey('group_one', $gstats);
		$this->assertArrayHasKey('group_two', $gstats);
		$this->assertCount(2, $gstats);
	}
}
