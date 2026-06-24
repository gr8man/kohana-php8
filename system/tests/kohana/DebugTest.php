<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.debug
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_DebugTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_debug()
	 *
	 * @return array
	 */
	public function provider_vars()
	{
		return array(
			// $thing, $expected
			array(array('foobar'), "<pre class=\"debug\"><small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span></pre>"),
		);
	}

	/**
	 * Tests Debug::vars()
	 *
	 * @test
	 * @dataProvider provider_vars
	 * @covers Debug::vars
	 * @param boolean $thing    The thing to debug
	 * @param boolean $expected Output for Debug::vars
	 */
	public function test_var($thing, $expected)
	{
		$this->assertEquals($expected, Debug::vars($thing));
	}

	/**
	 * Provides test data for testDebugPath()
	 *
	 * @return array
	 */
	public function provider_debug_path()
	{
		return array(
			array(
				SYSPATH.'classes'.DIRECTORY_SEPARATOR.'kohana'.EXT,
				'SYSPATH'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'kohana.php'
			),
			array(
				MODPATH.$this->dirSeparator('unittest/classes/kohana/unittest/runner').EXT,
				$this->dirSeparator('MODPATH/unittest/classes/kohana/unittest/runner').EXT
			),
		);
	}

	/**
	 * Tests Debug::path()
	 *
	 * @test
	 * @dataProvider provider_debug_path
	 * @covers Debug::path
	 * @param boolean $path     Input for Debug::path
	 * @param boolean $expected Output for Debug::path
	 */
	public function test_debug_path($path, $expected)
	{
		$this->assertEquals($expected, Debug::path($path));
	}

	/**
	 * Provides test data for test_dump()
	 *
	 * @return array
	 */
	public function provider_dump()
	{
		return array(
			array('foobar', 128, 10, '<small>string</small><span>(6)</span> "foobar"'),
			array('foobar', 2, 10, '<small>string</small><span>(6)</span> "fo&nbsp;&hellip;"'),
			array(null, 128, 10, '<small>NULL</small>'),
			array(true, 128, 10, '<small>bool</small> TRUE'),
			array(array('foobar'), 128, 10, "<small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span>"),
			array(new StdClass(), 128, 10, "<small>object</small> <span>stdClass(0)</span> <code>{\n}</code>"),
			array("fo\x6F\xFF\x00bar\x8F\xC2\xB110", 128, 10, '<small>string</small><span>(10)</span> "foobar±10"'),
			array(array('level1' => array('level2' => array('level3' => array('level4' => array('value' => 'something'))))), 128, 4,
'<small>array</small><span>(1)</span> <span>(
    "level1" => <small>array</small><span>(1)</span> <span>(
        "level2" => <small>array</small><span>(1)</span> <span>(
            "level3" => <small>array</small><span>(1)</span> <span>(
                "level4" => <small>array</small><span>(1)</span> (
                    ...
                )
            )</span>
        )</span>
    )</span>
)</span>'),
		);
	}

	/**
	 * Tests Debug::dump()
	 *
	 * @test
	 * @dataProvider provider_dump
	 * @covers Debug::dump
	 * @covers Debug::_dump
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_dump($input, $length, $limit, $expected)
	{
		$this->assertEquals($expected, Debug::dump($input, $length, $limit));
	}

	/**
	 * Tests Debug::trace() returns array
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_returns_array()
	{
		$trace = Debug::trace();
		$this->assertInternalType('array', $trace);
		$this->assertGreaterThan(0, count($trace));
	}

	/**
	 * Tests Debug::trace() with custom trace
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_with_custom_trace()
	{
		$custom = array(
			array('function' => 'test_func', 'file' => __FILE__, 'line' => __LINE__),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('test_func', $trace[0]['function']);
	}

	/**
	 * Tests Debug::trace() with static method call
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_static_method()
	{
		$custom = array(
			array(
				'function' => 'TestMethod',
				'class' => 'MyClass',
				'type' => '::',
				'args' => array('arg1'),
				'file' => __FILE__,
				'line' => __LINE__,
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('MyClass::TestMethod', $trace[0]['function']);
	}

	/**
	 * Tests Debug::trace() with closure
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_closure()
	{
		$custom = array(
			array(
				'function' => '{closure}',
				'args' => array('data'),
				'file' => __FILE__,
				'line' => __LINE__,
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertStringContainsString('{closure}', $trace[0]['function']);
	}

	/**
	 * Tests Debug::trace() with include statement
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_include()
	{
		$custom = array(
			array(
				'function' => 'include',
				'args' => array('/path/to/file.php'),
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('include', $trace[0]['function']);
	}

	/**
	 * Tests Debug::trace() with include_once statement
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_include_once()
	{
		$custom = array(
			array(
				'function' => 'include_once',
				'args' => array(__FILE__),
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('include_once', $trace[0]['function']);
	}

	/**
	 * Tests Debug::trace() skips invalid steps
	 *
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_skips_invalid_steps()
	{
		$custom = array(
			array(),
			array('function' => 'valid_func', 'file' => __FILE__, 'line' => __LINE__),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
	}

	/**
	 * Tests Debug::dump() with float
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_float()
	{
		$result = Debug::dump(3.14);
		$this->assertStringContainsString('float', $result);
		$this->assertStringContainsString('3.14', $result);
	}

	/**
	 * Tests Debug::dump() with integer zero
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_integer()
	{
		$result = Debug::dump(42);
		$this->assertStringContainsString('integer', $result);
		$this->assertStringContainsString('42', $result);
	}

	/**
	 * Tests Debug::dump() with FALSE
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_false()
	{
		$result = Debug::dump(false);
		$this->assertStringContainsString('bool', $result);
		$this->assertStringContainsString('FALSE', $result);
	}

	/**
	 * Tests Debug::vars() with no arguments returns NULL
	 *
	 * @test
	 * @covers Debug::vars
	 */
	public function test_vars_no_arguments()
	{
		$this->assertNull(Debug::vars());
	}

	/**
	 * Tests Debug::vars() with multiple arguments
	 *
	 * @test
	 * @covers Debug::vars
	 */
	public function test_vars_multiple_arguments()
	{
		$result = Debug::vars('hello', 42, null);
		$this->assertStringContainsString('hello', $result);
		$this->assertStringContainsString('42', $result);
		$this->assertStringContainsString('NULL', $result);
	}

	/**
	 * Tests Debug::dump() with resource
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_resource()
	{
		$resource = fopen(__FILE__, 'r');
		$result = Debug::dump($resource);
		$this->assertStringContainsString('resource', $result);
		fclose($resource);
	}

	/**
	 * Tests Debug::source() returns FALSE for unreadable file
	 *
	 * @test
	 * @covers Debug::source
	 */
	public function test_source_unreadable()
	{
		$this->assertFalse(Debug::source('', 1));
	}

	/**
	 * Tests Debug::source() returns formatted source
	 *
	 * @test
	 * @covers Debug::source
	 */
	public function test_source_valid()
	{
		$result = Debug::source(__FILE__, 5);
		$this->assertStringContainsString('<pre class="source">', $result);
	}

	/**
	 * Tests Debug::path() with DOCROOT path
	 *
	 * @test
	 * @covers Debug::path
	 */
	public function test_path_docroot()
	{
		$path = Debug::path(DOCROOT.'index.php');
		$this->assertStringContainsString('DOCROOT', $path);
	}

	/**
	 * Tests Debug::dump() with empty array
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_empty_array()
	{
		$result = Debug::dump(array());
		$this->assertStringContainsString('array', $result);
	}

	/**
	 * Tests Debug::dump() recursion detection in arrays
	 *
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_array_recursion()
	{
		$array = array('foo' => 'bar');
		$array['self'] = &$array;

		$result = Debug::dump($array);
		$this->assertStringContainsString('RECURSION', $result);
	}
}
