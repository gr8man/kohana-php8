<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Cache_MemcacheTag
 *
 * @group kohana
 * @group kohana.cache
 * @group kohana.cache.memcachetag
 * @package    Kohana/Cache
 * @category   Tests
 */
class Kohana_Cache_MemcacheTagTest extends Unittest_TestCase
{
	public function test_instantiation_without_tag_support_throws_exception(): void
	{
		if (! extension_loaded('memcache') && ! extension_loaded('memcached')) {
			$this->markTestSkipped('Neither memcache nor memcached extension is loaded');
		}

		$this->expectException(Cache_Exception::class);
		$ref = new ReflectionClass(Cache_MemcacheTag::class);
		$constructor = $ref->getConstructor();
		$instance = $ref->newInstanceWithoutConstructor();
		$constructor->invoke($instance, [
			'driver' => 'memcachetag',
			'servers' => [['host' => '127.0.0.1', 'port' => 11211]],
		]);
	}

	public function test_find_throws_exception(): void
	{
		$ref = new ReflectionClass(Cache_MemcacheTag::class);
		$instance = $ref->newInstanceWithoutConstructor();
		$this->expectException(Cache_Exception::class);
		$instance->find('some_tag');
	}
}
