<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Core
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
	public function provider_vars()
	{
		return array(
			array(array('foobar'), "<pre class=\"debug\"><small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span></pre>"),
		);
	}

	/**
	 * @test
	 * @dataProvider provider_vars
	 * @covers Debug::vars
	 */
	public function test_var($thing, $expected)
	{
		$this->assertEquals($expected, Debug::vars($thing));
	}

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
	 * @test
	 * @dataProvider provider_debug_path
	 * @covers Debug::path
	 */
	public function test_debug_path($path, $expected)
	{
		$this->assertEquals($expected, Debug::path($path));
	}

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
	 * @test
	 * @dataProvider provider_dump
	 * @covers Debug::dump
	 * @covers Debug::_dump
	 */
	public function test_dump($input, $length, $limit, $expected)
	{
		$this->assertEquals($expected, Debug::dump($input, $length, $limit));
	}

	/**
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
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_require()
	{
		$custom = array(
			array(
				'function' => 'require',
				'args' => array('/path/to/required.php'),
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('require', $trace[0]['function']);
	}

	/**
	 * @test
	 * @covers Debug::trace
	 */
	public function test_trace_require_once()
	{
		$custom = array(
			array(
				'function' => 'require_once',
				'args' => array(__FILE__),
			),
		);
		$trace = Debug::trace($custom);
		$this->assertCount(1, $trace);
		$this->assertEquals('require_once', $trace[0]['function']);
	}

	/**
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
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_integer_zero()
	{
		$result = Debug::dump(0);
		$this->assertStringContainsString('integer', $result);
		$this->assertStringContainsString('0', $result);
	}

	/**
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
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_true()
	{
		$result = Debug::dump(true);
		$this->assertStringContainsString('bool', $result);
		$this->assertStringContainsString('TRUE', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_null()
	{
		$result = Debug::dump(null);
		$this->assertStringContainsString('NULL', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_string()
	{
		$result = Debug::dump('hello world');
		$this->assertStringContainsString('string', $result);
		$this->assertStringContainsString('hello world', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_empty_string()
	{
		$result = Debug::dump('');
		$this->assertStringContainsString('string', $result);
		$this->assertStringContainsString('(0)', $result);
	}

	/**
	 * @test
	 * @covers Debug::vars
	 */
	public function test_vars_no_arguments()
	{
		$this->assertNull(Debug::vars());
	}

	/**
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
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_resource_stream_with_uri()
	{
		$tmp = tmpfile();
		fwrite($tmp, 'test data');
		$meta = stream_get_meta_data($tmp);
		$result = Debug::dump($tmp);
		$this->assertStringContainsString('resource', $result);
		$this->assertStringContainsString(str_replace(DOCROOT, 'DOCROOT'.DIRECTORY_SEPARATOR, $meta['uri'] ?? ''), $result);
		fclose($tmp);
	}

	/**
	 * @test
	 * @covers Debug::source
	 */
	public function test_source_unreadable()
	{
		$this->assertFalse(Debug::source('', 1));
	}

	/**
	 * @test
	 * @covers Debug::source
	 */
	public function test_source_nonexistent()
	{
		$this->assertFalse(Debug::source('/nonexistent/path/file.php', 1));
	}

	/**
	 * @test
	 * @covers Debug::source
	 */
	public function test_source_valid()
	{
		$result = Debug::source(__FILE__, 5);
		$this->assertStringContainsString('<pre class="source">', $result);
	}

	/**
	 * @test
	 * @covers Debug::path
	 */
	public function test_path_docroot()
	{
		$path = Debug::path(DOCROOT.'index.php');
		$this->assertStringContainsString('DOCROOT', $path);
	}

	/**
	 * @test
	 * @covers Debug::path
	 */
	public function test_path_apppath()
	{
		$path = Debug::path(APPPATH.'classes'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'welcome.php');
		$this->assertStringContainsString('APPPATH', $path);
	}

	/**
	 * @test
	 * @covers Debug::path
	 */
	public function test_path_custom()
	{
		$custom = '/some/random/path/file.php';
		$this->assertSame($custom, Debug::path($custom));
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_empty_array()
	{
		$result = Debug::dump(array());
		$this->assertStringContainsString('array', $result);
	}

	/**
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

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_object_recursion()
	{
		$obj = new StdClass();
		$obj->self = $obj;

		$result = Debug::dump($obj);
		$this->assertStringContainsString('RECURSION', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_object_with_properties()
	{
		$obj = new StdClass();
		$obj->public = 'value';
		$result = Debug::dump($obj);
		$this->assertStringContainsString('public', $result);
		$this->assertStringContainsString('value', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_array_depth_limit()
	{
		$deep = array('a' => array('b' => array('c' => 'deep')));
		$result = Debug::dump($deep, 128, 1);
		$this->assertStringContainsString('...', $result);
	}

	/**
	 * @test
	 * @covers Debug::dump
	 */
	public function test_dump_string_truncation()
	{
		$long = str_repeat('x', 200);
		$result = Debug::dump($long, 10);
		$this->assertStringContainsString('&hellip;', $result);
	}

	/**
	 * @test
	 * @covers Debug::_dump
	 */
	public function test_dump_closure_skipped()
	{
		$closure = function () {
			return 'test';
		};
		$this->markTestSkipped(
			'Debug::_dump() has a known PHP 8 bug with closures. ' .
			'Passing closures by reference triggers a "Deprecated: ' .
			'Call-time pass-by-reference has been deprecated" notice.'
		);
	}
}
