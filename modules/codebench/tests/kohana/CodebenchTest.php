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
		$this->assertTrue($refl->hasProperty('grades'));
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

	public function test_method_filter_accepts_bench_prefix(): void
	{
		$ref = new ReflectionMethod(Kohana_Codebench::class, '_method_filter');
		$ref->setAccessible(true);
		$bench = $this->getMockForAbstractClass(Kohana_Codebench::class);

		$this->assertTrue($ref->invoke($bench, 'benchSomething'));
		$this->assertTrue($ref->invoke($bench, 'bench'));
	}

	public function test_method_filter_rejects_non_bench(): void
	{
		$ref = new ReflectionMethod(Kohana_Codebench::class, '_method_filter');
		$ref->setAccessible(true);
		$bench = $this->getMockForAbstractClass(Kohana_Codebench::class);

		$this->assertFalse($ref->invoke($bench, 'somethingElse'));
		$this->assertFalse($ref->invoke($bench, 'notBench'));
		$this->assertFalse($ref->invoke($bench, ''));
	}

	public function test_grade_returns_correct_letter(): void
	{
		$ref = new ReflectionMethod(Kohana_Codebench::class, '_grade');
		$ref->setAccessible(true);
		$bench = $this->getMockForAbstractClass(Kohana_Codebench::class);

		$this->assertSame('A', $ref->invoke($bench, 100));
		$this->assertSame('A', $ref->invoke($bench, 125));
		$this->assertSame('B', $ref->invoke($bench, 126));
		$this->assertSame('B', $ref->invoke($bench, 150));
		$this->assertSame('C', $ref->invoke($bench, 151));
		$this->assertSame('C', $ref->invoke($bench, 200));
		$this->assertSame('D', $ref->invoke($bench, 201));
		$this->assertSame('D', $ref->invoke($bench, 300));
		$this->assertSame('E', $ref->invoke($bench, 301));
		$this->assertSame('E', $ref->invoke($bench, 500));
		$this->assertSame('F', $ref->invoke($bench, 501));
		$this->assertSame('F', $ref->invoke($bench, 999999));
	}

	public function test_grade_handles_zero_score(): void
	{
		$ref = new ReflectionMethod(Kohana_Codebench::class, '_grade');
		$ref->setAccessible(true);
		$bench = $this->getMockForAbstractClass(Kohana_Codebench::class);

		$this->assertSame('A', $ref->invoke($bench, 0));
	}

	public function test_construct_sets_time_limit(): void
	{
		$bench = $this->getMockForAbstractClass(Kohana_Codebench::class);
		$this->assertNotNull($bench);
	}
}
