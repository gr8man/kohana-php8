<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_HTTP
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_HTTPTest extends Unittest_TestCase
{
	public function test_www_form_urlencode_with_params(): void
	{
		$result = HTTP::www_form_urlencode(array('foo' => 'bar', 'baz' => 'qux'));
		$this->assertSame('foo=bar&baz=qux', $result);
	}

	public function test_www_form_urlencode_encodes_values(): void
	{
		$result = HTTP::www_form_urlencode(array('key' => 'value with spaces'));
		$this->assertSame('key=value%20with%20spaces', $result);
	}

	public function test_www_form_urlencode_empty_array(): void
	{
		$this->assertNull(HTTP::www_form_urlencode(array()));
	}

	public function test_www_form_urlencode_special_chars(): void
	{
		$result = HTTP::www_form_urlencode(array('q' => 'a+b&c=d'));
		$this->assertSame('q=a%2Bb%26c%3Dd', $result);
	}

	public function test_parse_header_string_simple(): void
	{
		$headers = HTTP::parse_header_string("Content-Type: text/html\r\n");
		$this->assertInstanceOf(HTTP_Header::class, $headers);
		$this->assertSame('text/html', (string) $headers['content-type']);
	}

	public function test_parse_header_string_multiple(): void
	{
		$headers = HTTP::parse_header_string("Content-Type: text/html\r\nX-Custom: value1\r\nAccept: */*\r\n");
		$this->assertSame('text/html', (string) $headers['content-type']);
		$this->assertSame('value1', (string) $headers['x-custom']);
		$this->assertSame('*/*', (string) $headers['accept']);
	}

	public function test_parse_header_string_duplicate(): void
	{
		$headers = HTTP::parse_header_string("X-Custom: first\r\nX-Custom: second\r\n");
		$this->assertIsArray($headers['x-custom']);
		$this->assertCount(2, $headers['x-custom']);
		$this->assertContains('first', $headers['x-custom']);
		$this->assertContains('second', $headers['x-custom']);
	}

	public function test_parse_header_string_empty(): void
	{
		$headers = HTTP::parse_header_string('');
		$this->assertInstanceOf(HTTP_Header::class, $headers);
	}

	public function test_request_headers_from_server(): void
	{
		$server = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_ACCEPT'] = 'text/html';
		$_SERVER['CONTENT_TYPE'] = 'application/json';

		$headers = HTTP::request_headers();
		$this->assertInstanceOf(HTTP_Header::class, $headers);
		$this->assertSame('example.com', (string) $headers['host']);
		$this->assertSame('application/json', (string) $headers['content-type']);

		$_SERVER = $server;
	}

	public function test_request_headers_with_content_length(): void
	{
		$server = $_SERVER;
		$_SERVER['CONTENT_LENGTH'] = '42';
		$_SERVER['HTTP_HOST'] = 'example.com';

		$headers = HTTP::request_headers();
		$this->assertSame('42', (string) $headers['content-length']);

		$_SERVER = $server;
	}

	public function test_request_headers_skips_non_http_server_keys(): void
	{
		$server = $_SERVER;
		$_SERVER = array(
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => '80',
			'CONTENT_TYPE' => 'text/plain',
		);

		$headers = HTTP::request_headers();
		$this->assertSame('text/plain', (string) $headers['content-type']);
		$this->assertFalse($headers->offsetExists('server_name'));

		$_SERVER = $server;
	}

	public function test_redirect_throws_redirect_exception(): void
	{
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
		$this->setEnvironment(array(
			'_SERVER' => array(
				'SERVER_NAME' => 'localhost',
				'HTTP_HOST' => 'localhost',
			) + $_SERVER,
		));
		$this->expectException(HTTP_Exception_Redirect::class);
		HTTP::redirect('/some/path', 302);
	}

	public function test_redirect_throws_for_invalid_code(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->expectExceptionMessage('Invalid redirect code');
		HTTP::redirect('/path', 400);
	}

	public function test_redirect_with_301(): void
	{
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
		$this->setEnvironment(array(
			'_SERVER' => array(
				'SERVER_NAME' => 'localhost',
				'HTTP_HOST' => 'localhost',
			) + $_SERVER,
		));
		$this->expectException(HTTP_Exception_Redirect::class);
		HTTP::redirect('/moved', 301);
	}

	public function test_redirect_with_303(): void
	{
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
		$this->setEnvironment(array(
			'_SERVER' => array(
				'SERVER_NAME' => 'localhost',
				'HTTP_HOST' => 'localhost',
			) + $_SERVER,
		));
		$this->expectException(HTTP_Exception_Redirect::class);
		HTTP::redirect('/see-other', 303);
	}

	public function test_redirect_with_307(): void
	{
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
		$this->setEnvironment(array(
			'_SERVER' => array(
				'SERVER_NAME' => 'localhost',
				'HTTP_HOST' => 'localhost',
			) + $_SERVER,
		));
		$this->expectException(HTTP_Exception_Redirect::class);
		HTTP::redirect('/temp', 307);
	}

	public function test_check_cache_adds_etag_and_cache_control(): void
	{
		$response = new Response();
		$response->body('response body');

		$request = new Request('test-route');

		$result = HTTP::check_cache($request, $response);

		$this->assertSame($response, $result);
		$this->assertNotNull($response->headers('etag'));
		$this->assertSame('must-revalidate', $response->headers('cache-control'));
	}

	public function test_check_cache_appends_to_existing_cache_control(): void
	{
		$response = new Response();
		$response->body('response body');
		$response->headers('cache-control', 'public');

		$request = new Request('test-route');

		$result = HTTP::check_cache($request, $response);

		$this->assertSame('public, must-revalidate', $response->headers('cache-control'));
	}

	public function test_check_cache_uses_provided_etag(): void
	{
		$response = new Response();
		$response->body('response body');

		$request = new Request('test-route');

		HTTP::check_cache($request, $response, '"custom-etag"');

		$this->assertSame('"custom-etag"', (string) $response->headers('etag'));
	}

	public function test_check_cache_throws_304_when_etag_matches(): void
	{
		$etag = '"matching-etag"';

		$response = new Response();
		$response->body('response body');

		$request = new Request('test-route');
		$request->headers('if-none-match', $etag);

		$this->expectException(HTTP_Exception_304::class);
		HTTP::check_cache($request, $response, $etag);
	}

	public function test_check_cache_throws_304_with_generated_etag(): void
	{
		$response = new Response();
		$response->body('response body');

		$request = new Request('test-route');
		$request->headers('if-none-match', $response->generate_etag());

		$this->expectException(HTTP_Exception_304::class);
		HTTP::check_cache($request, $response);
	}

	public function test_check_cache_does_not_throw_when_etag_differs(): void
	{
		$response = new Response();
		$response->body('response body');

		$request = new Request('test-route');
		$request->headers('if-none-match', '"non-matching"');

		$result = HTTP::check_cache($request, $response, '"actual-etag"');

		$this->assertSame($response, $result);
	}
}
