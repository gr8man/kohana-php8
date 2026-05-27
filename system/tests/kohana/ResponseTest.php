<?php

declare(strict_types=1); defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for response class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.response
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_ResponseTest extends Unittest_TestCase
{
	/**
	 * Provider for test_body
	 *
	 * @return array
	 */
	public function provider_body()
	{
		$view = $this->getMock('View');
		$view->expects($this->any())
			->method('__toString')
			->will($this->returnValue('foo'));

		return array(
			array('unit test', 'unit test'),
			array($view, 'foo'),
		);
	}

	/**
	 * Tests that we can set and read a body of a response
	 * 
	 * @test
	 * @dataProvider provider_body
	 *
	 * @return null
	 */
	public function test_body($source, $expected)
	{
		$response = new Response;
		$response->body($source);
		$this->assertSame($response->body(), $expected);

		$response = (string) $response;
		$this->assertSame($response, $expected);
	}

	/**
	 * Provides data for test_body_string_zero()
	 *
	 * @return array
	 */
	public function provider_body_string_zero()
	{
		return array(
			array('0', '0'),
			array("0", '0'),
			array(0, '0')
		);
	}

	/**
	 * Test that Response::body() handles numerics correctly
	 *
	 * @test
	 * @dataProvider provider_body_string_zero
	 * @param string $string 
	 * @param string $expected 
	 * @return void
	 */
	public function test_body_string_zero($string, $expected)
	{
		$response = new Response;
		$response->body($string);

		$this->assertSame($expected, $response->body());
	}

	/**
	 * provider for test_cookie_set()
	 *
	 * @return array
	 */
	public function provider_cookie_set()
	{
		return array(
			array(
				'test1',
				'foo',
				array(
					'test1' => array(
						'value' => 'foo',
						'expiration' => Cookie::$expiration
					),
				)
			),
			array(
				array(
					'test2' => 'stfu',
					'test3' => array(
						'value' => 'snafu',
						'expiration' => 123456789
					)
				),
				NULL,
				array(
					'test2' => array(
						'value' => 'stfu',
						'expiration' => Cookie::$expiration
					),
					'test3' => array(
						'value' => 'snafu',
						'expiration' => 123456789
					)
				)
			)
		);
	}

	/**
	 * Tests the Response::cookie() method, ensures
	 * correct values are set, including defaults
	 *
	 * @test
	 * @dataProvider provider_cookie_set
	 * @param string $key 
	 * @param string $value 
	 * @param string $expected 
	 * @return void
	 */
	public function test_cookie_set($key, $value, $expected)
	{
		// Setup the Response and apply cookie
		$response = new Response;
		$response->cookie($key, $value);

		foreach ($expected as $_key => $_value)
		{
			$cookie = $response->cookie($_key);

			$this->assertSame($_value['value'], $cookie['value']);
			$this->assertSame($_value['expiration'], $cookie['expiration']);
		}
	}

	/**
	 * Tests the Response::cookie() get functionality
	 *
	 * @return void
	 */
	public function test_cookie_get()
	{
		$response = new Response;

		// Test for empty cookies
		$this->assertSame(array(), $response->cookie());

		// Test for no specific cookie
		$this->assertNull($response->cookie('foobar'));

		$response->cookie('foo', 'bar');
		$cookie = $response->cookie('foo');

		$this->assertSame('bar', $cookie['value']);
		$this->assertSame(Cookie::$expiration, $cookie['expiration']);
	}

	/**
	 * Test the content type is sent when set
	 * 
	 * @test
	 */
	public function test_content_type_when_set()
	{
		$content_type = 'application/json';
		$response = new Response;
		$response->headers('content-type', $content_type);
		$headers  = $response->send_headers()->headers();
		$this->assertSame($content_type, (string) $headers['content-type']);
	}

	/**
	 * Tests Response::status() sets and gets status code
	 *
	 * @test
	 */
	public function test_status_code()
	{
		$response = new Response;
		$response->status(200);
		$this->assertSame(200, $response->status());
	}

	/**
	 * Tests Response::status() throws exception for invalid code
	 *
	 * @test
	 */
	public function test_status_invalid_code()
	{
		$this->expectException(\Kohana_Exception::class);
		$response = new Response;
		$response->status(99999);
	}

	/**
	 * Tests Response::protocol() sets and gets protocol
	 *
	 * @test
	 */
	public function test_protocol()
	{
		$response = new Response;
		$response->protocol('HTTP/1.0');
		$this->assertSame('HTTP/1.0', $response->protocol());

		$response->protocol('HTTP/1.1');
		$this->assertSame('HTTP/1.1', $response->protocol());
	}

	/**
	 * Tests Response::headers() returns HTTP_Header object
	 *
	 * @test
	 */
	public function test_headers_object()
	{
		$response = new Response;
		$this->assertInstanceOf('HTTP_Header', $response->headers());
	}

	/**
	 * Tests Response::headers() sets multiple headers at once
	 *
	 * @test
	 */
	public function test_headers_set_multiple()
	{
		$response = new Response;
		$response->headers(array(
			'X-Custom' => 'value1',
			'X-Other' => 'value2',
		));
		$this->assertSame('value1', (string) $response->headers('X-Custom'));
		$this->assertSame('value2', (string) $response->headers('X-Other'));
	}

	/**
	 * Tests Response::headers() returns NULL for unknown header
	 *
	 * @test
	 */
	public function test_headers_unknown()
	{
		$response = new Response;
		$this->assertNull($response->headers('X-Nonexistent'));
	}

	/**
	 * Tests Response::cookie() returns all cookies when called without args
	 *
	 * @test
	 */
	public function test_cookie_get_all()
	{
		$response = new Response;
		$response->cookie('a', '1');
		$response->cookie('b', '2');
		$cookies = $response->cookie();
		$this->assertCount(2, $cookies);
		$this->assertArrayHasKey('a', $cookies);
		$this->assertArrayHasKey('b', $cookies);
	}

	/**
	 * Tests Response::delete_cookie() removes a cookie
	 *
	 * @test
	 */
	public function test_cookie_delete()
	{
		$response = new Response;
		$response->cookie('test', 'value');
		$response->delete_cookie('test');
		$this->assertNull($response->cookie('test'));
	}

	/**
	 * Tests Response::body() with new Response returns empty string
	 *
	 * @test
	 */
	public function test_body_default_empty()
	{
		$response = new Response;
		$this->assertSame('', $response->body());
	}

	/**
	 * Tests Response::body() chainability
	 *
	 * @test
	 */
	public function test_body_chain()
	{
		$response = new Response;
		$result = $response->body('content');
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::status() chainability
	 *
	 * @test
	 */
	public function test_status_chain()
	{
		$response = new Response;
		$result = $response->status(200);
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::headers() chainability
	 *
	 * @test
	 */
	public function test_headers_chain()
	{
		$response = new Response;
		$result = $response->headers('X-Test', 'value');
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::send_headers() returns response
	 *
	 * @test
	 */
	public function test_send_headers_returns_response()
	{
		$response = new Response;
		$result = $response->send_headers();
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::body() with zero integer
	 *
	 * @test
	 */
	public function test_body_zero_integer()
	{
		$response = new Response;
		$response->body(0);
		$this->assertSame('0', $response->body());
	}
}
