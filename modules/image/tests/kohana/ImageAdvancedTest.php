<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Image advanced features (watermark, background, reflection,
 * sharpen, save, render) using mocks — no driver required.
 *
 * @group kohana
 * @group kohana.image
 *
 * @package    Kohana/Image
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_ImageAdvancedTest extends Unittest_TestCase
{
	public static function setUpBeforeClass(): void
	{
		$modules = Kohana::modules();
		if (! isset($modules['image'])) {
			$modules['image'] = MODPATH.'image';
			Kohana::modules($modules);
		}
	}

	/**
	 * Helper to create a test image mock from the abstract Image class
	 */
	protected function _createImageMock($width, $height): Image
	{
		$mock = $this->getMockBuilder(Image::class)
			->disableOriginalConstructor()
			->onlyMethods(array(
				'_do_resize', '_do_crop', '_do_rotate', '_do_flip',
				'_do_sharpen', '_do_reflection', '_do_watermark',
				'_do_background', '_do_save', '_do_render',
			))
			->getMock();

		$refl = new ReflectionClass($mock);
		$refl_width = $refl->getProperty('width');
		$refl_width->setValue($mock, $width);

		$refl_height = $refl->getProperty('height');
		$refl_height->setValue($mock, $height);

		$refl_file = $refl->getProperty('file');
		$refl_file->setValue($mock, '/tmp/test.jpg');

		$refl_type = $refl->getProperty('type');
		$refl_type->setValue($mock, IMAGETYPE_JPEG);

		$refl_mime = $refl->getProperty('mime');
		$refl_mime->setValue($mock, 'image/jpeg');

		return $mock;
	}

	/**
	 * Watermark: default params → center offset
	 */
	public function test_watermark_default_position(): void
	{
		$image = $this->_createImageMock(800, 600);
		$watermark = $this->_createImageMock(100, 100);

		$image->expects($this->once())
			->method('_do_watermark')
			->with($this->identicalTo($watermark), $this->equalTo(350), $this->equalTo(250), $this->equalTo(100));

		$image->watermark($watermark);
	}

	/**
	 * Watermark: offset (0, 0) → top-left
	 */
	public function test_watermark_top_left(): void
	{
		$image = $this->_createImageMock(800, 600);
		$watermark = $this->_createImageMock(100, 100);

		$image->expects($this->once())
			->method('_do_watermark')
			->with($this->identicalTo($watermark), $this->equalTo(0), $this->equalTo(0), $this->equalTo(100));

		$image->watermark($watermark, 0, 0);
	}

	/**
	 * Watermark: offset (true, true) → bottom-right
	 */
	public function test_watermark_bottom_right(): void
	{
		$image = $this->_createImageMock(800, 600);
		$watermark = $this->_createImageMock(100, 100);

		$image->expects($this->once())
			->method('_do_watermark')
			->with($this->identicalTo($watermark), $this->equalTo(700), $this->equalTo(500), $this->equalTo(100));

		$image->watermark($watermark, true, true);
	}

	/**
	 * Watermark: offset (null, null) → center
	 */
	public function test_watermark_centered(): void
	{
		$image = $this->_createImageMock(800, 600);
		$watermark = $this->_createImageMock(100, 100);

		$image->expects($this->once())
			->method('_do_watermark')
			->with($this->identicalTo($watermark), $this->equalTo(350), $this->equalTo(250), $this->equalTo(100));

		$image->watermark($watermark);
	}

	/**
	 * Background: hex color with hash
	 */
	public function test_background_hex_color(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(255), $this->equalTo(0), $this->equalTo(0), $this->equalTo(100));

		$image->background('#ff0000');
	}

	/**
	 * Background: shorthand hex with custom quality
	 */
	public function test_background_with_quality(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(255), $this->equalTo(255), $this->equalTo(255), $this->equalTo(80));

		$image->background('#fff', 80);
	}

	/**
	 * Background: default quality is 100
	 */
	public function test_background_default_quality(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(0), $this->equalTo(0), $this->equalTo(0), $this->equalTo(100));

		$image->background('#000');
	}

	/**
	 * Reflection: default height (full image), default opacity, no fade-in
	 */
	public function test_reflection_default_height(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(600), $this->equalTo(100), $this->equalTo(false));

		$image->reflection();
	}

	/**
	 * Reflection: custom height, opacity, fade-in
	 */
	public function test_reflection_custom_values(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(30), $this->equalTo(75), $this->equalTo(50));

		$image->reflection(30, 75, 50);
	}

	/**
	 * Reflection: height value that exceeds image height is clamped
	 */
	public function test_reflection_full_height(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(600), $this->equalTo(50), $this->equalTo(100));

		$image->reflection(1000, 50, 100);
	}

	/**
	 * Sharpen: normal value
	 */
	public function test_sharpen(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(50));

		$image->sharpen(50);
	}

	/**
	 * Sharpen: value below 1 is clamped to 1
	 */
	public function test_sharpen_clamp_min(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(1));

		$image->sharpen(0);
	}

	/**
	 * Sharpen: value above 100 is clamped to 100
	 */
	public function test_sharpen_clamp_max(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(100));

		$image->sharpen(150);
	}

	/**
	 * Save: file path only, quality defaults to 100
	 */
	public function test_save(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_save')
			->with($this->equalTo('test.jpg'), $this->equalTo(100));

		$image->save('test.jpg');
	}

	/**
	 * Save: file path with custom quality
	 */
	public function test_save_with_quality(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_save')
			->with($this->equalTo('test.jpg'), $this->equalTo(80));

		$image->save('test.jpg', 80);
	}

	/**
	 * Render: custom type and quality
	 */
	public function test_render(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_render')
			->with($this->equalTo('jpg'), $this->equalTo(90));

		$image->render('jpg', 90);
	}

	/**
	 * Render: default type derived from image type, default quality
	 */
	public function test_render_default_type(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_render')
			->with($this->equalTo('jpeg'), $this->equalTo(100));

		$image->render();
	}

	/**
	 * Constants: resize master dimension constants exist and are integers
	 */
	public function test_constants_exist(): void
	{
		$this->assertIsInt(Image::AUTO);
		$this->assertIsInt(Image::INVERSE);
		$this->assertIsInt(Image::WIDTH);
		$this->assertIsInt(Image::HEIGHT);
		$this->assertIsInt(Image::NONE);
		$this->assertIsInt(Image::PRECISE);
	}

	/**
	 * Constants: crop-related constants exist (same master dimension set)
	 */
	public function test_crop_constants(): void
	{
		$this->assertIsInt(Image::NONE);
		$this->assertIsInt(Image::WIDTH);
		$this->assertIsInt(Image::HEIGHT);
		$this->assertIsInt(Image::AUTO);
		$this->assertIsInt(Image::INVERSE);
		$this->assertIsInt(Image::PRECISE);
	}

	/**
	 * Constants: flip / orientation constants exist and are integers
	 */
	public function test_orientation_constants(): void
	{
		$this->assertIsInt(Image::HORIZONTAL);
		$this->assertIsInt(Image::VERTICAL);
	}
}
