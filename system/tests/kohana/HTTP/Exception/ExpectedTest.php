<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_HTTP_Exception_Expected
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http_exception
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class HTTP_Exception_Expected_TestStub extends HTTP_Exception_Expected
{
	protected $_code = 300;
}

#[AllowDynamicProperties]
class Kohana_HTTP_Exception_ExpectedTest extends Unittest_TestCase
{
	public function test_construct_sets_response_status(): void
	{
		$e = new HTTP_Exception_Expected_TestStub('test message');
		$ref_response = new ReflectionProperty($e, '_response');
		$ref_response->setAccessible(true);
		$response = $ref_response->getValue($e);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(300, $response->status());
	}

	public function test_headers_set_and_get(): void
	{
		$e = new HTTP_Exception_Expected_TestStub();
		$result = $e->headers('X-Custom', 'value1');
		$this->assertSame($e, $result);
		$this->assertSame('value1', $e->headers('X-Custom'));
	}

	public function test_headers_returns_header_value(): void
	{
		$e = new HTTP_Exception_Expected_TestStub();
		$e->headers('Content-Type', 'application/json');
		$this->assertSame('application/json', $e->headers('Content-Type'));
	}

	public function test_check_returns_true(): void
	{
		$e = new HTTP_Exception_Expected_TestStub();
		$this->assertTrue($e->check());
	}

	public function test_get_response_returns_response(): void
	{
		$e = new HTTP_Exception_Expected_TestStub('error');
		$response = $e->get_response();
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(300, $response->status());
	}

	public function test_construct_with_all_params(): void
	{
		$previous = new Exception('prev');
		$e = new HTTP_Exception_Expected_TestStub('msg :var', array(':var' => 'val'), $previous);
		$this->assertStringContainsString('msg val', $e->getMessage());
	}
}
