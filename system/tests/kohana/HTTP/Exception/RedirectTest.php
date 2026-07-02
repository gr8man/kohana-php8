<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_HTTP_Exception_Redirect
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http_exception
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class HTTP_Exception_Redirect_TestStub extends HTTP_Exception_Redirect
{
	protected $_code = 302;
}

#[AllowDynamicProperties]
class Kohana_HTTP_Exception_RedirectTest extends Unittest_TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$_SERVER['SERVER_NAME'] = 'localhost';
		$_SERVER['SERVER_PORT'] = '80';
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
	}

	public function test_location_get_returns_null_when_not_set(): void
	{
		$e = new HTTP_Exception_Redirect_TestStub();
		$this->assertNull($e->location());
	}

	public function test_location_set_and_get_with_relative_uri(): void
	{
		$e = new HTTP_Exception_Redirect_TestStub();
		$result = $e->location('/some/uri');
		$this->assertSame($e, $result);
		$location = $e->location();
		$this->assertStringContainsString('/some/uri', $location);
		$this->assertStringContainsString('localhost', $location);
	}

	public function test_location_with_full_url(): void
	{
		$e = new HTTP_Exception_Redirect_TestStub();
		$e->location('https://example.com/page');
		$this->assertSame('https://example.com/page', $e->location());
	}

	public function test_check_passes_when_location_set(): void
	{
		$e = new HTTP_Exception_Redirect_TestStub();
		$e->location('/somewhere');
		$this->assertTrue($e->check());
	}

	public function test_check_throws_when_location_not_set(): void
	{
		$this->expectException(Kohana_Exception::class);
		$this->expectExceptionMessage('location');
		$e = new HTTP_Exception_Redirect_TestStub();
		$e->check();
	}

	public function test_get_response_returns_response_with_location_header(): void
	{
		$e = new HTTP_Exception_Redirect_TestStub('redirecting');
		$e->location('https://example.com/target');
		$response = $e->get_response();
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(302, $response->status());
	}
}
