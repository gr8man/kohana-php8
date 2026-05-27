<?php

declare(strict_types=1); defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Exception Class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.exception
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ExceptionTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_constructor()
	 *
	 * @return array
	 */
	public function provider_constructor()
	{
		return array(
			array(array(''), '', 0),
			array(array(':a'), ':a', 0),

			array(array(':a', NULL), ':a', 0),
			array(array(':a', array()), ':a', 0),
			array(array(':a', array(':a' => 'b')), 'b', 0),
			array(array(':a :b', array(':a' => 'c', ':b' => 'd')), 'c d', 0),

			array(array(':a', NULL, 5), ':a', 5),
			// #3358
			array(array(':a', NULL, '3F000'), ':a', '3F000'),
			// #3404
			array(array(':a', NULL, '42S22'), ':a', '42S22'),
			// #3927
			array(array(':a', NULL, 'b'), ':a', 'b'),
			// #4039
			array(array(':a', NULL, '25P01'), ':a', '25P01'),
		);
	}

	/**
	 * Tests Kohana_Kohana_Exception::__construct()
	 *
	 * @test
	 * @dataProvider provider_constructor
	 * @covers Kohana_Kohana_Exception::__construct
	 * @param array             $arguments          Arguments
	 * @param string            $expected_message   Value from getMessage()
	 * @param integer|string    $expected_code      Value from getCode()
	 */
	public function test_constructor($arguments, $expected_message, $expected_code)
	{
		switch (count($arguments))
		{
			case 1:
				$exception = new Kohana_Exception(reset($arguments));
			break;
			case 2:
				$exception = new Kohana_Exception(reset($arguments), next($arguments));
			break;
			default:
				$exception = new Kohana_Exception(reset($arguments), next($arguments), next($arguments));
		}

		$this->assertSame($expected_code, $exception->getCode());
		$this->assertSame($expected_message, $exception->getMessage());
	}

	/**
	 * Provides test data for test_text()
	 *
	 * @return array
	 */
	public function provider_text()
	{
		return array(
			array(new Kohana_Exception('foobar'), $this->dirSeparator('Kohana_Exception [ 0 ]: foobar ~ SYSPATH/tests/kohana/ExceptionTest.php [ '.__LINE__.' ]')),
		);
	}

	/**
	 * Tests Kohana_Exception::text()
	 *
	 * @test
	 * @dataProvider provider_text
	 * @covers Kohana_Exception::text
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_text($exception, $expected)
	{
		$this->assertEquals($expected, Kohana_Exception::text($exception));
	}

	/**
	 * Tests Kohana_Exception::text() with ErrorException
	 *
	 * @test
	 * @covers Kohana_Exception::text
	 */
	public function test_text_with_error_exception()
	{
		$exception = new ErrorException('warning', E_WARNING, 0, __FILE__, __LINE__);
		$text = Kohana_Exception::text($exception);

		$this->assertStringContainsString('ErrorException', $text);
		$this->assertStringContainsString('warning', $text);
	}

	/**
	 * Tests Kohana_Exception::log()
	 *
	 * @test
	 * @covers Kohana_Exception::log
	 */
	public function test_log()
	{
		$exception = new Kohana_Exception('test message');
		Kohana_Exception::log($exception, Log::ERROR);

		// Should not throw, log is written to Kohana::$log
		$this->assertTrue(TRUE);
	}

	/**
	 * Tests Kohana_Exception::response() returns a Response object
	 *
	 * @test
	 * @covers Kohana_Exception::response
	 */
	public function test_response_returns_response_object()
	{
		$exception = new Kohana_Exception('test error');
		$response = Kohana_Exception::response($exception);

		$this->assertInstanceOf('Response', $response);
		$this->assertEquals(500, $response->status());
	}

	/**
	 * Tests Kohana_Exception::response() with HTTP exception
	 *
	 * @test
	 * @covers Kohana_Exception::response
	 */
	public function test_response_with_http_exception()
	{
		$exception = HTTP_Exception::factory(404, 'Page not found');
		$response = Kohana_Exception::response($exception);

		$this->assertInstanceOf('Response', $response);
		$this->assertEquals(404, $response->status());
	}

	/**
	 * Tests Kohana_Exception::_handler() returns response for Throwable
	 *
	 * @test
	 * @covers Kohana_Exception::_handler
	 */
	public function test_handler_internal()
	{
		$response = Kohana_Exception::_handler(new RuntimeException('internal test'));

		$this->assertInstanceOf('Response', $response);
		$this->assertEquals(500, $response->status());
	}

	/**
	 * Tests Kohana_Exception::text() handles exception with no message
	 *
	 * @test
	 * @covers Kohana_Exception::text
	 */
	public function test_text_no_message()
	{
		$exception = new Kohana_Exception('');
		$text = Kohana_Exception::text($exception);

		$this->assertStringContainsString('Kohana_Exception', $text);
	}
}
