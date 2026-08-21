<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for HTTP Exceptions
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http
 * @group kohana.core.http.exception
 * @package    Kohana
 * @category   Tests
 */
class Kohana_Http_ExceptionTest extends Unittest_TestCase
{
	public function test_factory_creates_correct_exception(): void
	{
		$codes = array(
			300, 301, 302, 303, 304, 305, 307,
			400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
			500, 501, 502, 503, 504, 505,
		);

		foreach ($codes as $code) {
			$e = HTTP_Exception::factory($code, 'Message for :code', array(':code' => $code));
			$this->assertInstanceOf(HTTP_Exception::class, $e);
			$this->assertSame($code, $e->getCode());
		}
	}

	public function test_http_exception_305(): void
	{
		$e = new HTTP_Exception_305();
		$e->location('http://proxy.example.com:8080');
		$this->assertSame('http://proxy.example.com:8080', $e->location());
		$this->assertTrue($e->check());

		$e_invalid = new HTTP_Exception_305();
		$this->expectException(Kohana_Exception::class);
		$e_invalid->check();
	}

	public function test_http_exception_401(): void
	{
		$e = new HTTP_Exception_401();
		$e->authenticate('Basic realm="Test"');
		$this->assertSame('Basic realm="Test"', $e->authenticate());
		$this->assertTrue($e->check());

		$e_invalid = new HTTP_Exception_401();
		$this->expectException(Kohana_Exception::class);
		$e_invalid->check();
	}

	public function test_http_exception_405(): void
	{
		$e = new HTTP_Exception_405();
		$e->allowed(array('GET', 'POST'));
		$this->assertSame('GET,POST', (string) $e->headers('allow'));
		$this->assertTrue($e->check());

		$e_invalid = new HTTP_Exception_405();
		$this->expectException(Kohana_Exception::class);
		$e_invalid->check();
	}
}
