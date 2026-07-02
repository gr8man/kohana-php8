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
		Fragment::delete('test_nonexistent');
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

	public function test_load_and_save_cycle(): void
	{
		$name = 'test_cycle_' . uniqid();
		Fragment::delete($name);

		$result = Fragment::load($name);
		$this->assertFalse($result);

		$content = 'cached content for ' . $name;
		echo $content;

		Fragment::save();

		while (ob_get_level() > 1) {
			ob_end_clean();
		}

		ob_start();
		$cached = Fragment::load($name);
		$cached_output = ob_get_clean();
		$this->assertTrue($cached);
		$this->assertSame($content, $cached_output);

		Fragment::delete($name);
	}

	public function test_delete_removes_cached_fragment(): void
	{
		$name = 'test_delete_' . uniqid();

		Fragment::delete($name);
		$result = Fragment::load($name);
		$this->assertFalse($result);
		echo 'to delete';
		Fragment::save();

		Fragment::delete($name);

		$result2 = Fragment::load($name);
		$this->assertFalse($result2);
		while (ob_get_level() > 1) {
			ob_end_clean();
		}
	}

	public function test_load_with_explicit_lifetime(): void
	{
		$name = 'test_lifetime_' . uniqid();
		Fragment::delete($name);

		$result = Fragment::load($name, 60);
		$this->assertFalse($result);
		echo 'lifetime test';
		Fragment::save();

		while (ob_get_level() > 1) {
			ob_end_clean();
		}

		$result2 = Fragment::load($name, 60);
		$this->assertTrue($result2);

		Fragment::delete($name);
	}

	public function test_load_with_i18n_true(): void
	{
		$name = 'test_i18n_' . uniqid();
		Fragment::delete($name);

		$result = Fragment::load($name, null, true);
		$this->assertFalse($result);
		echo 'i18n content';
		Fragment::save();

		while (ob_get_level() > 1) {
			ob_end_clean();
		}

		$result2 = Fragment::load($name, null, true);
		$this->assertTrue($result2);

		Fragment::delete($name);
	}

	public function test_load_with_i18n_false_override(): void
	{
		Fragment::$i18n = true;
		$name = 'test_i18n_false_' . uniqid();
		Fragment::delete($name);

		$result = Fragment::load($name, null, false);
		$this->assertFalse($result);
		echo 'no i18n';
		Fragment::save();

		while (ob_get_level() > 1) {
			ob_end_clean();
		}

		$result2 = Fragment::load($name, null, false);
		$this->assertTrue($result2);

		Fragment::delete($name);
		Fragment::$i18n = false;
	}
}
