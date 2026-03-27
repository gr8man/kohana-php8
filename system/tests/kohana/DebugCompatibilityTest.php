<?php

declare(strict_types=1);

defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Debug class PHP 8.3 compatibility fixes
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.debug
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_DebugCompatibilityTest extends Unittest_TestCase
{
	public function provider_debug_dump_types()
	{
		return array(
			array(NULL, '<small>NULL</small>'),
			array(TRUE, '<small>bool</small> TRUE'),
			array(FALSE, '<small>bool</small> FALSE'),
			array(0, '<small>int</small> 0'),
			array(42, '<small>int</small> 42'),
			array(3.14, '<small>float</small>'),
			array('', '<small>string</small><span>(0)</span> ""'),
			array('hello', '<small>string</small>'),
			array(array(), '<small>array</small><span>(0)</span>'),
			array(array(1, 2, 3), '<small>array</small><span>(3)</span>'),
		);
	}

	/**
	 * Tests Debug::dump with various PHP types
	 *
	 * @test
	 * @dataProvider provider_debug_dump_types
	 */
	public function test_debug_dump_handles_php_types($value, $expected_pattern)
	{
		$result = Debug::dump($value);
		
		$this->assertIsString($result);
		$this->assertStringContainsString('<small>', $result);
	}

	public function test_debug_dump_strings()
	{
		$str = 'Hello World';
		$result = Debug::dump($str);
		
		$this->assertStringContainsString('Hello World', $result);
		$this->assertStringContainsString('string', $result);
	}

	public function test_debug_dump_arrays()
	{
		$arr = array('key' => 'value', 'num' => 123);
		$result = Debug::dump($arr);
		
		$this->assertStringContainsString('array', $result);
		$this->assertStringContainsString('key', $result);
		$this->assertStringContainsString('value', $result);
	}

	public function test_debug_dump_objects()
	{
		$obj = new stdClass();
		$obj->property = 'value';
		$result = Debug::dump($obj);
		
		$this->assertStringContainsString('object', $result);
		$this->assertStringContainsString('stdClass', $result);
	}

	public function test_debug_dump_nested_structures()
	{
		$data = array(
			'level1' => array(
				'level2' => array(
					'value' => 'deep',
				),
			),
		);
		
		$result = Debug::dump($data);
		
		$this->assertStringContainsString('level1', $result);
		$this->assertStringContainsString('level2', $result);
		$this->assertStringContainsString('deep', $result);
	}

	public function test_debug_vars()
	{
		$result = Debug::vars('test1', 'test2', 123);
		
		$this->assertStringContainsString('test1', $result);
		$this->assertStringContainsString('test2', $result);
		$this->assertStringContainsString('123', $result);
		$this->assertStringContainsString('debug', $result);
	}

	public function test_debug_vars_with_object()
	{
		$obj = new stdClass();
		$obj->name = 'test';
		
		$result = Debug::vars($obj);
		
		$this->assertStringContainsString('stdClass', $result);
		$this->assertStringContainsString('name', $result);
		$this->assertStringContainsString('test', $result);
	}

	public function test_debug_vars_empty()
	{
		$result = Debug::vars();
		
		$this->assertNull($result);
	}

	public function test_debug_trace_with_exception()
	{
		try {
			throw new Exception('Test exception');
		}
		catch (Exception $e) {
			$trace = Debug::trace();
			
			$this->assertIsArray($trace);
			$this->assertNotEmpty($trace);
		}
	}

	public function test_debug_trace_with_custom_trace()
	{
		$custom_trace = array(
			array(
				'function' => 'test_function',
				'file' => '/path/to/file.php',
				'line' => 10,
			),
		);
		
		$result = Debug::trace($custom_trace);
		
		$this->assertIsArray($result);
		$this->assertCount(1, $result);
	}

	public function test_debug_source_file_not_found()
	{
		$result = Debug::source('/nonexistent/file.php', 1);
		
		$this->assertFalse($result);
	}

	public function test_debug_source_with_valid_file()
	{
		$result = Debug::source(__FILE__, 5, 2);
		
		$this->assertIsString($result);
		$this->assertStringContainsString('source', $result);
	}

	public function test_debug_source_with_padding()
	{
		$result = Debug::source(__FILE__, 5, 5);
		
		if ($result !== FALSE) {
			$this->assertStringContainsString('source', $result);
		}
	}

	public function test_debug_path_with_app_path()
	{
		$path = APPPATH . 'classes/Controller/Welcome.php';
		$result = Debug::path($path);
		
		$this->assertStringContainsString('APPPATH', $result);
	}

	public function test_debug_path_with_sys_path()
	{
		$path = SYSPATH . 'classes/Kohana/Core.php';
		$result = Debug::path($path);
		
		$this->assertStringContainsString('SYSPATH', $result);
	}

	public function test_debug_dump_recursion()
	{
		$arr = array('a');
		$arr[] = &$arr;
		
		$result = Debug::dump($arr);
		
		$this->assertStringContainsString('*RECURSION*', $result);
	}

	public function test_debug_dump_object_recursion()
	{
		$obj1 = new stdClass();
		$obj2 = new stdClass();
		$obj1->ref = $obj2;
		$obj2->ref = $obj1;
		
		$result = Debug::dump($obj1);
		
		$this->assertIsString($result);
	}

	public function test_debug_dump_depth_limit()
	{
		$deep_array = array('level1' => array('level2' => array('level3' => array())));
		
		$result = Debug::dump($deep_array, 128, 1);
		
		$this->assertIsString($result);
	}

	public function test_debug_dump_with_length_parameter()
	{
		$long_string = str_repeat('a', 200);
		$result = Debug::dump($long_string, 50);
		
		$this->assertStringContainsString('&hellip;', $result);
		$this->assertStringContainsString('string', $result);
		$this->assertStringContainsString('(200)', $result);
	}

	public function test_debug_trace_handles_internal_functions()
	{
		$trace = array(
			array(
				'function' => 'include',
				'file' => '/test.php',
				'line' => 1,
			),
			array(
				'function' => 'require',
				'file' => '/test.php',
				'line' => 2,
			),
		);
		
		$result = Debug::trace($trace);
		
		$this->assertIsArray($result);
	}

	public function test_debug_trace_handles_class_methods()
	{
		$trace = array(
			array(
				'class' => 'TestClass',
				'type' => '->',
				'function' => 'testMethod',
				'file' => '/test.php',
				'line' => 10,
			),
		);
		
		$result = Debug::trace($trace);
		
		$this->assertIsArray($result);
		$this->assertStringContainsString('TestClass', $result[0]['function']);
	}

	public function test_debug_trace_handles_static_methods()
	{
		$trace = array(
			array(
				'class' => 'TestClass',
				'type' => '::',
				'function' => 'staticMethod',
				'file' => '/test.php',
				'line' => 10,
			),
		);
		
		$result = Debug::trace($trace);
		
		$this->assertIsArray($result);
	}

	public function test_debug_trace_handles_closures()
	{
		$trace = array(
			array(
				'function' => '{closure}',
				'file' => '/test.php',
				'line' => 10,
			),
		);
		
		$result = Debug::trace($trace);
		
		$this->assertIsArray($result);
	}
}
