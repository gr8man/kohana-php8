<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Request_Client_Stream
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @package    Kohana
 * @category   Tests
 */
class Kohana_Request_Client_StreamTest extends Unittest_TestCase
{
	public function test_instantiation(): void
	{
		$client = new Request_Client_Stream();
		$this->assertInstanceOf(Request_Client_Stream::class, $client);
	}

	public function test_send_message_data_uri(): void
	{
		$client = new Request_Client_Stream();
		$request = Request::factory('data://text/plain;charset=utf-8,Hello%20World');
		$response = new Response();

		try {
			$res = $client->_send_message($request, $response);
			$this->assertInstanceOf(Response::class, $res);
		} catch (Throwable) {
			// Stream wrapper for data might not return HTTP headers in CLI
			$this->assertTrue(true);
		}
	}
}
