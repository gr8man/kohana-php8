<?php

declare(strict_types=1);
include_once(Kohana::find_file('tests/cache', 'CacheBasicMethodsTest'));

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_SqliteTest extends Kohana_CacheBasicMethodsTest {

	/**
     * This method MUST be implemented by each driver to setup the `Cache`
     * instance for each test.
     *
     * This method should do the following tasks for each driver test:
     *
     *  - Test the Cache instance driver is available, skip test otherwise
     *  - Setup the Cache instance
     *  - Call the parent setup method, `parent::setUp()`
     */
    public function setUp(): void
	{
		parent::setUp();

		if ( ! extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('SQLite PDO PHP Extension is not available');
		}

		if ( ! Kohana::$config->load('cache.sqlite'))
		{
			Kohana::$config->load('cache')
				->set(
					'sqlite',
					[
						'driver'             => 'sqlite',
						'default_expire'     => 3600,
						'database'           => 'memory',
						'schema'             => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
					]
				);
		}

		$this->cache(Cache::instance('sqlite'));
	}

	public function test_tagging_and_find(): void
	{
		$cache = $this->cache();
		$this->assertTrue($cache->set_with_tags('tag_test_1', 'value1', 3600, ['tag_a', 'tag_b']));
		$this->assertTrue($cache->set_with_tags('tag_test_2', 'value2', 3600, ['tag_b', 'tag_c']));

		$found_b = $cache->find('tag_b');
		$this->assertArrayHasKey('tag_test_1', $found_b);
		$this->assertArrayHasKey('tag_test_2', $found_b);

		$found_a = $cache->find('tag_a');
		$this->assertArrayHasKey('tag_test_1', $found_a);
		$this->assertArrayNotHasKey('tag_test_2', $found_a);

		$this->assertTrue($cache->delete_tag('tag_a'));
		$this->assertNull($cache->get('tag_test_1'));
	}

	public function test_garbage_collect(): void
	{
		$cache = $this->cache();
		$cache->set('expired_key', 'expired_val', -100);
		$cache->garbage_collect();
		$this->assertNull($cache->get('expired_key'));
	}
} // End Kohana_SqliteTest
