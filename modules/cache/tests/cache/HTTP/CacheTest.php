<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for HTTP_Cache
 *
 * @group kohana
 * @group kohana.cache
 * @group kohana.cache.http
 * @package    Kohana/Cache
 * @category   Tests
 */
class Kohana_Cache_HTTP_CacheTest extends Unittest_TestCase
{
	public function test_factory(): void
	{
		$cache = Cache::instance('file');
		$http_cache = HTTP_Cache::factory($cache, ['allow_private_cache' => true]);
		$this->assertInstanceOf(HTTP_Cache::class, $http_cache);
	}

	public function test_basic_cache_key_generator(): void
	{
		$request = Request::factory('http://example.com/api/test?param=1');
		$key = HTTP_Cache::basic_cache_key_generator($request);
		$this->assertIsString($key);
		$this->assertSame(40, strlen($key));
	}

	public function test_create_cache_key(): void
	{
		$cache = Cache::instance('file');
		$http_cache = new HTTP_Cache(['cache' => $cache]);
		$request = Request::factory('http://example.com/api/test');
		$key = $http_cache->create_cache_key($request);
		$this->assertIsString($key);
	}

	public function test_getters_and_setters(): void
	{
		$cache = Cache::instance('file');
		$http_cache = new HTTP_Cache();

		$this->assertSame($http_cache, $http_cache->cache($cache));
		$this->assertSame($cache, $http_cache->cache());

		$this->assertSame($http_cache, $http_cache->allow_private_cache(true));
		$this->assertTrue($http_cache->allow_private_cache());

		$this->assertSame($http_cache, $http_cache->cache_key_callback('HTTP_Cache::basic_cache_key_generator'));
		$this->assertSame('HTTP_Cache::basic_cache_key_generator', $http_cache->cache_key_callback());
	}

	public function test_invalidate_cache_and_destructive_execute(): void
	{
		$cache = Cache::instance('file');
		$http_cache = new HTTP_Cache(['cache' => $cache]);
		$request = Request::factory('http://example.com/api/test');
		$http_cache->invalidate_cache($request);

		$request_post = Request::factory('http://example.com/api/test')
			->method(HTTP_Request::POST);
		$client = new Request_Client_Internal();
		$response = new Response();

		$result = $http_cache->execute($client, $request_post, $response);
		$this->assertInstanceOf(Response::class, $result);
	}
}
