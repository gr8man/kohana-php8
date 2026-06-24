<?php

declare(strict_types=1);

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_CacheTest extends PHPUnit_Framework_TestCase {

	const BAD_GROUP_DEFINITION  = 1010;
	const EXPECT_SELF           = 1001;

	/**
	 * Data provider for test_instance
	 *
	 * @return  array
	 */
	public function provider_instance()
	{
		realpath(sys_get_temp_dir());

		$base = [];

		if (Kohana::$config->load('cache.file'))
		{
			$base = [
				// Test default group
				[
					NULL,
					Cache::instance('file')
				],
				// Test defined group
				[
					'file',
					Cache::instance('file')
				],
			];
		}


		return [
			// Test bad group definition
			$base+[
				Kohana_CacheTest::BAD_GROUP_DEFINITION,
				'Failed to load Kohana Cache group: 1010'
			],
		];
	}

	/**
     * Tests the [Cache::factory()] method behaves as expected
     *
     * @dataProvider provider_instance
     */
    public function test_instance($group, $expected): void
	{
		if (in_array($group, [
			Kohana_CacheTest::BAD_GROUP_DEFINITION,
			]
		))
		{
			$this->setExpectedException('Cache_Exception');
		}

		try
		{
			$cache = Cache::instance($group);
		}
		catch (Cache_Exception $e)
		{
			$this->assertSame($expected, $e->getMessage());
			throw $e;
		}

		$this->assertInstanceOf($expected::class, $cache);
		$this->assertSame($expected->config(), $cache->config());
	}

	/**
     * Tests that `clone($cache)` will be prevented to maintain singleton
     *
     * @expectedException Cache_Exception
     */
    public function test_cloning_fails(): void
    {
        $this->getMockBuilder('Cache')
			->disableOriginalConstructor()
			->getMockForAbstractClass();
    }

	/**
	 * Data provider for test_config
	 *
	 * @return  array
	 */
	public function provider_config()
	{
		return [
			[
				[
					'server'     => 'otherhost',
					'port'       => 5555,
					'persistent' => TRUE,
				],
				NULL,
				Kohana_CacheTest::EXPECT_SELF,
				[
					'server'     => 'otherhost',
					'port'       => 5555,
					'persistent' => TRUE,
				],
			],
			[
				'foo',
				'bar',
				Kohana_CacheTest::EXPECT_SELF,
				[
					'foo'        => 'bar'
				]
			],
			[
				'server',
				NULL,
				NULL,
				[]
			],
			[
				NULL,
				NULL,
				[],
				[]
			]
		];
	}

	/**
     * Tests the config method behaviour
     *
     * @dataProvider provider_config
     *
     * @param   mixed    key value to set or get
     * @param   mixed    value to set to key
     * @param   mixed    expected result from [Cache::config()]
     * @param   array    expected config within cache
     */
    public function test_config($key, $value, $expected_result, array $expected_config): void
	{
		$cache = $this->getMock('Cache_File', NULL, [], '', FALSE);

		if ($expected_result === Kohana_CacheTest::EXPECT_SELF)
		{
			$expected_result = $cache;
		}

		$this->assertSame($expected_result, $cache->config($key, $value));
		$this->assertSame($expected_config, $cache->config());
	}

	/**
	 * Data provider for test_sanitize_id
	 *
	 * @return  array
	 */
	public function provider_sanitize_id()
	{
		return [
			[
				'foo',
				'foo'
			],
			[
				'foo+-!@',
				'foo+-!@'
			],
			[
				'foo/bar',
				'foo_bar',
			],
			[
				'foo\\bar',
				'foo_bar'
			],
			[
				'foo bar',
				'foo_bar'
			],
			[
				'foo\\bar snafu/stfu',
				'foo_bar_snafu_stfu'
			]
		];
	}

	/**
     * Tests the [Cache::_sanitize_id()] method works as expected.
     * This uses some nasty reflection techniques to access a protected
     * method.
     *
     * @dataProvider provider_sanitize_id
     *
     * @param   string    id
     * @param   string    expected
     */
    public function test_sanitize_id($id, $expected): void
	{
		$cache = $this->getMock('Cache', [
			'get',
			'set',
			'delete',
			'delete_all'
			], [[]],
			'', FALSE
		);

		$cache_reflection = new ReflectionClass($cache);
		$sanitize_id = $cache_reflection->getMethod('_sanitize_id');

		$this->assertSame($expected, $sanitize_id->invoke($cache, $id));
	}
} // End Kohana_CacheTest
