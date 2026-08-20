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
}
