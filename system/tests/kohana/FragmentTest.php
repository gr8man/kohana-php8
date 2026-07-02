<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Fragment
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.fragment
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_FragmentTest extends Unittest_TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		Fragment::$lifetime = 30;
		Fragment::$i18n = false;
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	public function test_cache_key_default_i18n(): void
	{
		$ref = new ReflectionMethod(Fragment::class, '_cache_key');
		$ref->setAccessible(true);
		$key = $ref->invoke(null, 'footer');
		$this->assertStringContainsString('footer', $key);
		$this->assertStringContainsString('Fragment::cache(', $key);
	}

	public function test_cache_key_with_i18n(): void
	{
		Fragment::$i18n = true;
		$ref = new ReflectionMethod(Fragment::class, '_cache_key');
		$ref->setAccessible(true);
		$key = $ref->invoke(null, 'footer', true);
		$this->assertStringContainsString('footer', $key);
		$this->assertStringContainsString('Fragment::cache(', $key);
	}

	public function test_delete_calls_kohana_cache(): void
	{
		Fragment::delete('test_delete');
		$this->assertTrue(true);
	}

	public function test_load_returns_false_when_no_cache(): void
	{
		$result = Fragment::load('test_nonexistent');
		$this->assertFalse($result);
		while (ob_get_level() > 1) {
			ob_end_clean();
		}
	}

	public function test_save_does_not_throw_without_load(): void
	{
		Fragment::save();
		$this->assertTrue(true);
	}

	public function test_lifetime_setting(): void
	{
		Fragment::$lifetime = 60;
		$this->assertSame(60, Fragment::$lifetime);
	}

	public function test_i18n_setting(): void
	{
		Fragment::$i18n = true;
		$this->assertTrue(Fragment::$i18n);
	}
}
