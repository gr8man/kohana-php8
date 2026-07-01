<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Codebench module
 *
 * @group kohana
 * @group kohana.codebench
 *
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_CodebenchTest extends Unittest_TestCase
{
	public static function setUpBeforeClass(): void
	{
		$modules = Kohana::modules();
		if (! isset($modules['codebench'])) {
			$modules['codebench'] = MODPATH.'codebench';
			Kohana::modules($modules);
		}
	}

	public function test_codebench_extends_kohana_codebench(): void
	{
		$refl = new ReflectionClass(Codebench::class);
		$this->assertTrue($refl->isSubclassOf(Kohana_Codebench::class));
	}

	public function test_codebench_is_abstract(): void
	{
		$refl = new ReflectionClass(Kohana_Codebench::class);
		$this->assertTrue($refl->isAbstract());
	}

	public function test_codebench_has_expected_properties(): void
	{
		$refl = new ReflectionClass(Kohana_Codebench::class);

		$this->assertTrue($refl->hasProperty('loops'));
		$this->assertTrue($refl->hasProperty('subjects'));
		$this->assertTrue($refl->hasProperty('benchmarks'));
	}

	public function test_arr_callback_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_ArrCallback'));
	}

	public function test_auto_link_emails_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_AutoLinkEmails'));
	}

	public function test_date_span_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_DateSpan'));
	}

	public function test_explode_limit_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_ExplodeLimit'));
	}

	public function test_gruber_url_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_GruberURL'));
	}

	public function test_ltrim_digits_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_LtrimDigits'));
	}

	public function test_md_do_base_url_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_MDDoBaseURL'));
	}

	public function test_md_do_image_url_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_MDDoImageURL'));
	}

	public function test_md_do_include_views_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_MDDoIncludeViews'));
	}

	public function test_strip_null_bytes_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_StripNullBytes'));
	}

	public function test_transliterate_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_Transliterate'));
	}

	public function test_url_site_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_URLSite'));
	}

	public function test_user_func_array_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_UserFuncArray'));
	}

	public function test_valid_color_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_ValidColor'));
	}

	public function test_valid_url_bench_exists(): void
	{
		$this->assertTrue(class_exists('Bench_ValidURL'));
	}

	public function test_controller_codebench_exists(): void
	{
		$this->assertTrue(class_exists('Controller_Codebench'));
	}

	public function test_codebench_config_exists(): void
	{
		$config = Kohana::$config->load('codebench');
		$this->assertNotNull($config);
	}

	public function test_codebench_init_file_exists(): void
	{
		$init_file = MODPATH . 'codebench/init.php';
		$this->assertFileExists($init_file);
	}
}
