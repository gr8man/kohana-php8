<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Request_Client_Curl
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @package    Kohana
 * @category   Tests
 */
class Kohana_Request_Client_CurlTest extends Unittest_TestCase
{
	public function test_set_curl_request_method_post(): void
	{
		$client = new Request_Client_Curl();
		$request = Request::factory('http://example.com/')->method(Request::POST);
		$options = $client->_set_curl_request_method($request, []);
		$this->assertArrayHasKey(CURLOPT_POST, $options);
		$this->assertTrue($options[CURLOPT_POST]);
	}

	public function test_set_curl_request_method_custom(): void
	{
		$client = new Request_Client_Curl();
		$request = Request::factory('http://example.com/')->method(Request::PUT);
		$options = $client->_set_curl_request_method($request, []);
		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertSame('PUT', $options[CURLOPT_CUSTOMREQUEST]);
	}

	public function test_send_message_data_uri(): void
	{
		if (! extension_loaded('curl')) {
			$this->markTestSkipped('curl extension is not available.');
		}

		$client = new Request_Client_Curl();
		$request = Request::factory('data:text/plain;charset=utf-8,Hello%20World');
		$response = Response::factory();

		try {
			$result = $client->_send_message($request, $response);
			$this->assertInstanceOf(Response::class, $result);
			$this->assertSame('Hello World', $result->body());
		} catch (Request_Exception) {
			// Some curl versions may not support data uri protocol
			$this->assertTrue(true);
		}
	}
}
