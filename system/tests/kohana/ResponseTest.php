<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

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
		$response = new Response();
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
		$response = new Response();
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
				null,
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
		$response = new Response();
		$response->cookie($key, $value);

		foreach ($expected as $_key => $_value) {
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
		$response = new Response();

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
		$response = new Response();
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
		$response = new Response();
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
		$response = new Response();
		$response->status(99999);
	}

	/**
	 * Tests Response::protocol() sets and gets protocol
	 *
	 * @test
	 */
	public function test_protocol()
	{
		$response = new Response();
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
		$response = new Response();
		$this->assertInstanceOf('HTTP_Header', $response->headers());
	}

	/**
	 * Tests Response::headers() sets multiple headers at once
	 *
	 * @test
	 */
	public function test_headers_set_multiple()
	{
		$response = new Response();
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
		$response = new Response();
		$this->assertNull($response->headers('X-Nonexistent'));
	}

	/**
	 * Tests Response::cookie() returns all cookies when called without args
	 *
	 * @test
	 */
	public function test_cookie_get_all()
	{
		$response = new Response();
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
		$response = new Response();
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
		$response = new Response();
		$this->assertSame('', $response->body());
	}

	/**
	 * Tests Response::body() chainability
	 *
	 * @test
	 */
	public function test_body_chain()
	{
		$response = new Response();
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
		$response = new Response();
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
		$response = new Response();
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
		$response = new Response();
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
		$response = new Response();
		$response->body(0);
		$this->assertSame('0', $response->body());
	}

	/**
	 * Tests Response::factory() returns Response instance
	 *
	 * @test
	 */
	public function test_factory()
	{
		$response = Response::factory();
		$this->assertInstanceOf('Response', $response);
	}

	/**
	 * Tests Response::factory() with config array
	 *
	 * @test
	 */
	public function test_factory_with_config()
	{
		$response = Response::factory(array('_status' => 404));
		$this->assertSame(404, $response->status());
	}

	/**
	 * Tests Response::__construct() with header config
	 *
	 * @test
	 */
	public function test_construct_with_header_config()
	{
		$response = new Response(array(
			'_header' => array('X-Custom' => 'foo'),
		));
		$this->assertSame('foo', (string) $response->headers('X-Custom'));
	}

	/**
	 * Tests Response::__construct() with body config
	 *
	 * @test
	 */
	public function test_construct_with_body_config()
	{
		$response = new Response(array('_body' => 'hello'));
		$this->assertSame('hello', $response->body());
	}

	/**
	 * Tests Response::__construct() with protocol config
	 *
	 * @test
	 */
	public function test_construct_with_protocol_config()
	{
		$response = new Response(array('_protocol' => 'HTTP/2.0'));
		$this->assertSame('HTTP/2.0', $response->protocol());
	}

	/**
	 * Tests Response::__toString() returns body
	 *
	 * @test
	 */
	public function test_to_string()
	{
		$response = new Response();
		$response->body('test content');
		$this->assertSame('test content', (string) $response);
	}

	/**
	 * Tests Response::__toString() with empty body
	 *
	 * @test
	 */
	public function test_to_string_empty()
	{
		$response = new Response();
		$this->assertSame('', (string) $response);
	}

	/**
	 * Tests Response::protocol() returns default protocol
	 *
	 * @test
	 */
	public function test_protocol_default()
	{
		$response = new Response();
		$this->assertSame(HTTP::$protocol, $response->protocol());
	}

	/**
	 * Tests Response::protocol() chainability
	 *
	 * @test
	 */
	public function test_protocol_chain()
	{
		$response = new Response();
		$result = $response->protocol('HTTP/1.1');
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::protocol() converts to uppercase
	 *
	 * @test
	 */
	public function test_protocol_uppercase()
	{
		$response = new Response();
		$response->protocol('http/1.0');
		$this->assertSame('HTTP/1.0', $response->protocol());
	}

	/**
	 * Tests Response::status() defaults to 200
	 *
	 * @test
	 */
	public function test_status_default()
	{
		$response = new Response();
		$this->assertSame(200, $response->status());
	}

	/**
	 * Tests Response::status() with various valid codes
	 *
	 * @test
	 */
	public function test_status_various()
	{
		$response = new Response();
		$response->status(404);
		$this->assertSame(404, $response->status());

		$response->status(500);
		$this->assertSame(500, $response->status());

		$response->status(301);
		$this->assertSame(301, $response->status());

		$response->status(204);
		$this->assertSame(204, $response->status());
	}

	/**
	 * Tests Response::headers() returns single header string
	 *
	 * @test
	 */
	public function test_headers_get_single()
	{
		$response = new Response();
		$response->headers('Content-Type', 'text/plain');
		$this->assertSame('text/plain', (string) $response->headers('Content-Type'));
	}

	/**
	 * Tests Response::content_length()
	 *
	 * @test
	 */
	public function test_content_length()
	{
		$response = new Response();
		$response->body('hello');
		$this->assertSame(5, $response->content_length());
	}

	/**
	 * Tests Response::content_length() with empty body
	 *
	 * @test
	 */
	public function test_content_length_empty()
	{
		$response = new Response();
		$this->assertSame(0, $response->content_length());
	}

	/**
	 * Tests Response::content_length() after body update
	 *
	 * @test
	 */
	public function test_content_length_after_update()
	{
		$response = new Response();
		$response->body('hello');
		$this->assertSame(5, $response->content_length());
		$response->body('hi');
		$this->assertSame(2, $response->content_length());
	}

	/**
	 * Tests Response::cookie() with array input for multiple cookies
	 *
	 * @test
	 */
	public function test_cookie_set_array()
	{
		$response = new Response();
		$response->cookie(array(
			'test1' => 'value1',
			'test2' => array(
				'value' => 'value2',
				'expiration' => 999999,
			),
		));
		$cookies = $response->cookie();
		$this->assertCount(2, $cookies);
		$this->assertSame('value1', $cookies['test1']['value']);
		$this->assertSame('value2', $cookies['test2']['value']);
		$this->assertSame(999999, $cookies['test2']['expiration']);
	}

	/**
	 * Tests Response::delete_cookies() removes all cookies
	 *
	 * @test
	 */
	public function test_delete_cookies()
	{
		$response = new Response();
		$response->cookie('a', '1');
		$response->cookie('b', '2');
		$response->delete_cookies();
		$this->assertSame(array(), $response->cookie());
	}

	/**
	 * Tests Response::delete_cookies() chainability
	 *
	 * @test
	 */
	public function test_delete_cookies_chain()
	{
		$response = new Response();
		$result = $response->delete_cookies();
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::delete_cookie() chainability
	 *
	 * @test
	 */
	public function test_delete_cookie_chain()
	{
		$response = new Response();
		$response->cookie('a', '1');
		$result = $response->delete_cookie('a');
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::send_headers() with replace parameter
	 *
	 * @test
	 */
	public function test_send_headers_with_replace()
	{
		$response = new Response();
		$result = $response->send_headers(true);
		$this->assertSame($response, $result);
	}

	/**
	 * Tests Response::send_headers() with callback
	 *
	 * @test
	 */
	public function test_send_headers_with_callback()
	{
		$response = new Response();
		$response->status(200);
		$response->protocol('HTTP/1.1');

		$captured = null;
		$callback = function ($resp, $headers, $replace) use (&$captured) {
			$captured = $headers;
			return $resp;
		};

		$result = $response->send_headers(false, $callback);
		$this->assertSame($response, $result);
		$this->assertNotNull($captured);
		$this->assertIsArray($captured);
		$this->assertStringContainsString('200 OK', $captured[0]);
	}

	/**
	 * Tests Response::render() basic output
	 *
	 * @test
	 */
	public function test_render_basic()
	{
		$orig_protocol = HTTP::$protocol;
		$orig_content_type = Kohana::$content_type;
		$orig_charset = Kohana::$charset;
		$orig_expose = Kohana::$expose;

		HTTP::$protocol = 'HTTP/1.1';
		Kohana::$content_type = 'text/html';
		Kohana::$charset = 'UTF-8';
		Kohana::$expose = false;

		$response = new Response();
		$response->status(200);
		$response->protocol('HTTP/1.1');
		$response->body('test body');

		$output = $response->render();

		$this->assertStringContainsString('HTTP/1.1 200 OK', $output);
		$this->assertStringContainsString('Content-Type: text/html; charset=UTF-8', $output);
		$this->assertStringContainsString('Content-Length: 9', $output);
		$this->assertStringContainsString('test body', $output);

		HTTP::$protocol = $orig_protocol;
		Kohana::$content_type = $orig_content_type;
		Kohana::$charset = $orig_charset;
		Kohana::$expose = $orig_expose;
	}

	/**
	 * Tests Response::render() with cookies
	 *
	 * @test
	 */
	public function test_render_with_cookies()
	{
		$orig_protocol = HTTP::$protocol;
		$orig_content_type = Kohana::$content_type;
		$orig_charset = Kohana::$charset;
		$orig_expose = Kohana::$expose;

		HTTP::$protocol = 'HTTP/1.1';
		Kohana::$content_type = 'text/html';
		Kohana::$charset = 'UTF-8';
		Kohana::$expose = false;

		$response = new Response();
		$response->status(200);
		$response->body('body');
		$response->cookie('session', 'abc123');

		$output = $response->render();

		$this->assertStringContainsString('session=abc123', $output);
		$this->assertStringContainsString('set-cookie', strtolower($output));

		HTTP::$protocol = $orig_protocol;
		Kohana::$content_type = $orig_content_type;
		Kohana::$charset = $orig_charset;
		Kohana::$expose = $orig_expose;
	}

	/**
	 * Tests Response::render() preserves custom content-type
	 *
	 * @test
	 */
	public function test_render_custom_content_type()
	{
		$orig_protocol = HTTP::$protocol;
		$orig_content_type = Kohana::$content_type;
		$orig_charset = Kohana::$charset;
		$orig_expose = Kohana::$expose;

		HTTP::$protocol = 'HTTP/1.1';
		Kohana::$content_type = 'text/html';
		Kohana::$charset = 'UTF-8';
		Kohana::$expose = false;

		$response = new Response();
		$response->status(200);
		$response->headers('content-type', 'application/json');
		$response->body('{}');

		$output = $response->render();

		$this->assertStringContainsString('application/json', $output);
		$this->assertStringNotContainsString('text/html', $output);

		HTTP::$protocol = $orig_protocol;
		Kohana::$content_type = $orig_content_type;
		Kohana::$charset = $orig_charset;
		Kohana::$expose = $orig_expose;
	}

	/**
	 * Tests Response::render() with Kohana expose
	 *
	 * @test
	 */
	public function test_render_with_expose()
	{
		$orig_protocol = HTTP::$protocol;
		$orig_content_type = Kohana::$content_type;
		$orig_charset = Kohana::$charset;
		$orig_expose = Kohana::$expose;

		HTTP::$protocol = 'HTTP/1.1';
		Kohana::$content_type = 'text/html';
		Kohana::$charset = 'UTF-8';
		Kohana::$expose = true;

		$response = new Response();
		$response->status(200);
		$response->body('content');

		$output = $response->render();

		$this->assertStringContainsString('user-agent:', strtolower($output));

		HTTP::$protocol = $orig_protocol;
		Kohana::$content_type = $orig_content_type;
		Kohana::$charset = $orig_charset;
		Kohana::$expose = $orig_expose;
	}

	/**
	 * Tests Response::generate_etag() generates etag
	 *
	 * @test
	 */
	public function test_generate_etag()
	{
		$orig_protocol = HTTP::$protocol;
		$orig_content_type = Kohana::$content_type;
		$orig_charset = Kohana::$charset;
		$orig_expose = Kohana::$expose;

		HTTP::$protocol = 'HTTP/1.1';
		Kohana::$content_type = 'text/html';
		Kohana::$charset = 'UTF-8';
		Kohana::$expose = false;

		$response = new Response();
		$response->body('content');
		$etag = $response->generate_etag();

		$this->assertStringStartsWith('"', $etag);
		$this->assertStringEndsWith('"', $etag);
		$this->assertSame(42, strlen($etag));

		HTTP::$protocol = $orig_protocol;
		Kohana::$content_type = $orig_content_type;
		Kohana::$charset = $orig_charset;
		Kohana::$expose = $orig_expose;
	}

	/**
	 * Tests Response::generate_etag() throws for empty body
	 *
	 * @test
	 */
	public function test_generate_etag_empty_body()
	{
		$this->expectException(\Request_Exception::class);
		$response = new Response();
		$response->generate_etag();
	}

	/**
	 * Tests Response::_parse_byte_range() returns false when no header
	 *
	 * @test
	 */
	public function test_parse_byte_range_no_header()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		unset($_SERVER['HTTP_RANGE']);

		$response = new Response();
		$method = new ReflectionMethod($response, '_parse_byte_range');
		$method->setAccessible(true);
		$result = $method->invoke($response);
		$this->assertFalse($result);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		}
	}

	/**
	 * Tests Response::_parse_byte_range() parses valid range
	 *
	 * @test
	 */
	public function test_parse_byte_range_valid()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=100-499';

		$response = new Response();
		$method = new ReflectionMethod($response, '_parse_byte_range');
		$method->setAccessible(true);
		$result = $method->invoke($response);

		$this->assertIsArray($result);
		$this->assertSame('100', $result[1]);
		$this->assertSame('499', $result[2]);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_parse_byte_range() parses range without end
	 *
	 * @test
	 */
	public function test_parse_byte_range_open_ended()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=500-';

		$response = new Response();
		$method = new ReflectionMethod($response, '_parse_byte_range');
		$method->setAccessible(true);
		$result = $method->invoke($response);

		$this->assertIsArray($result);
		$this->assertSame('500-', $result[1]);
		$this->assertArrayNotHasKey(2, $result);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_parse_byte_range() parses negative range
	 *
	 * @test
	 */
	public function test_parse_byte_range_negative()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=-500';

		$response = new Response();
		$method = new ReflectionMethod($response, '_parse_byte_range');
		$method->setAccessible(true);
		$result = $method->invoke($response);

		$this->assertIsArray($result);
		$this->assertSame('-500', $result[1]);
		$this->assertArrayNotHasKey(2, $result);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() returns full range by default
	 *
	 * @test
	 */
	public function test_calculate_byte_range_no_range()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		unset($_SERVER['HTTP_RANGE']);

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(0, $start);
		$this->assertSame(999, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() with positive start range
	 *
	 * @test
	 */
	public function test_calculate_byte_range_positive_start()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=500-';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(500, $start);
		$this->assertSame(999, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() with negative start range
	 *
	 * @test
	 */
	public function test_calculate_byte_range_negative_start()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=-500';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(500, $start);
		$this->assertSame(999, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() with full range
	 *
	 * @test
	 */
	public function test_calculate_byte_range_full()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=0-499';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(0, $start);
		$this->assertSame(499, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() out of bounds end
	 *
	 * @test
	 */
	public function test_calculate_byte_range_out_of_bounds()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=0-9999';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(0, $start);
		$this->assertSame(999, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() when start exceeds end
	 *
	 * @test
	 */
	public function test_calculate_byte_range_inverted()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=800-500';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(0, $start);
		$this->assertSame(500, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	/**
	 * Tests Response::_calculate_byte_range() with start at zero
	 *
	 * @test
	 */
	public function test_calculate_byte_range_start_zero()
	{
		$orig_range = $_SERVER['HTTP_RANGE'] ?? null;
		$_SERVER['HTTP_RANGE'] = 'bytes=0-0';

		$response = new Response();
		$method = new ReflectionMethod($response, '_calculate_byte_range');
		$method->setAccessible(true);
		list($start, $end) = $method->invoke($response, 1000);

		$this->assertSame(0, $start);
		$this->assertSame(0, $end);

		if ($orig_range !== null) {
			$_SERVER['HTTP_RANGE'] = $orig_range;
		} else {
			unset($_SERVER['HTTP_RANGE']);
		}
	}

	public function test_cookies_manipulation(): void
	{
		$response = new Response();
		$response->cookie('test_name', 'test_value');
		$this->assertIsArray($response->cookie());
		$this->assertSame('test_value', $response->cookie('test_name')['value']);

		$response->delete_cookie('test_name');
		$this->assertNull($response->cookie('test_name'));

		$response->cookie(array('c1' => 'v1', 'c2' => 'v2'));
		$this->assertCount(2, $response->cookie());

		$response->delete_cookies();
		$this->assertEmpty($response->cookie());
	}

	public function test_generate_etag(): void
	{
		$response = new Response();
		$response->body('Hello ETag');
		$etag = $response->generate_etag();
		$this->assertIsString($etag);
		$this->assertStringStartsWith('"', $etag);
		$this->assertStringEndsWith('"', $etag);
	}

	public function test_generate_etag_empty_body_throws_exception(): void
	{
		$this->expectException(Request_Exception::class);
		$response = new Response();
		$response->generate_etag();
	}

	public function test_send_headers_with_callback(): void
	{
		$response = new Response();
		$response->headers('X-Custom-Header', 'custom_value');
		$captured = array();
		$response->send_headers(true, function ($header) use (&$captured) {
			$captured[] = $header;
		});
		$this->assertNotEmpty($captured);
	}
}
