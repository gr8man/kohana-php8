<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Image resize/crop calculations (no GD required)
 *
 * These tests verify the algorithmic logic of Image::resize(), Image::crop(),
 * Image::rotate(), Image::flip(), etc. without needing any image driver.
 *
 * @group kohana
 * @group kohana.image
 * @group kohana.image.resize
 *
 * @package    Kohana/Image
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_ImageResizeTest extends Unittest_TestCase
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

	public function test_resize_auto_master(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(400), $this->equalTo(300));

		$image->resize(400, 400, Image::AUTO);
	}

	public function test_resize_width_master(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(400), $this->equalTo(300));

		$image->resize(400);
	}

	public function test_resize_height_master(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(400), $this->equalTo(300));

		$image->resize(null, 300);
	}

	public function test_resize_none(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(200), $this->equalTo(100));

		$image->resize(200, 100, Image::NONE);
	}

	public function test_resize_inverse(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(400), $this->equalTo(300));

		$image->resize(400, 400, Image::INVERSE);
	}

	public function test_resize_precise(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(400), $this->equalTo(300));

		$image->resize(400, 400, Image::PRECISE);
	}

	public function test_resize_width_only(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(200), $this->equalTo(150));

		$image->resize(200);
	}

	public function test_resize_wider_than_tall_auto(): void
	{
		$image = $this->_createImageMock(1600, 1200);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(200), $this->equalTo(150));

		$image->resize(200, 200, Image::AUTO);
	}

	public function test_resize_taller_than_wide_auto(): void
	{
		$image = $this->_createImageMock(600, 800);

		$image->expects($this->once())
			->method('_do_resize')
			->with($this->equalTo(300), $this->equalTo(400));

		$image->resize(300, 300, Image::AUTO);
	}

	public function test_crop_center(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(200), $this->equalTo(200), $this->equalTo(300), $this->equalTo(200));

		$image->crop(200, 200);
	}

	public function test_crop_top_left(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(200), $this->equalTo(200), $this->equalTo(0), $this->equalTo(0));

		$image->crop(200, 200, 0, 0);
	}

	public function test_crop_bottom_right(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(200), $this->equalTo(200), $this->equalTo(600), $this->equalTo(400));

		$image->crop(200, 200, true, true);
	}

	public function test_crop_negative_offset_x(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(200), $this->equalTo(200), $this->equalTo(400), $this->equalTo(200));

		$image->crop(200, 200, -200);
	}

	public function test_crop_negative_offset_y(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(200), $this->equalTo(200), $this->equalTo(300), $this->equalTo(200));

		$image->crop(200, 200, null, -200);
	}

	public function test_crop_larger_than_image_uses_max(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_crop')
			->with($this->equalTo(800), $this->equalTo(600), $this->equalTo(0), $this->equalTo(0));

		$image->crop(2000, 2000);
	}

	public function test_rotate_normalize_positive(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(45));

		$image->rotate(45);
	}

	public function test_rotate_normalize_negative(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(-90));

		$image->rotate(-90);
	}

	public function test_rotate_wrap_around(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(0));

		$image->rotate(360);
	}

	public function test_rotate_wrap_around_negative(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(0));

		$image->rotate(-360);
	}

	public function test_rotate_large_positive(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(30));

		$image->rotate(750);
	}

	public function test_rotate_large_negative(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_rotate')
			->with($this->equalTo(-30));

		$image->rotate(-750);
	}

	public function test_flip_horizontal(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_flip')
			->with($this->equalTo(Image::HORIZONTAL));

		$image->flip(Image::HORIZONTAL);
	}

	public function test_flip_vertical(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_flip')
			->with($this->equalTo(Image::VERTICAL));

		$image->flip(Image::VERTICAL);
	}

	public function test_flip_invalid_defaults_to_vertical(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_flip')
			->with($this->equalTo(Image::VERTICAL));

		$image->flip(999);
	}

	public function test_sharpen_clamps_low(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(1));

		$image->sharpen(0);
	}

	public function test_sharpen_clamps_high(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(100));

		$image->sharpen(200);
	}

	public function test_sharpen_normal(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_sharpen')
			->with($this->equalTo(50));

		$image->sharpen(50);
	}

	public function test_background_hex_with_hash(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(255), $this->equalTo(0), $this->equalTo(0), $this->equalTo(100));

		$image->background('#ff0000');
	}

	public function test_background_hex_without_hash(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(0), $this->equalTo(0), $this->equalTo(0), $this->equalTo(50));

		$image->background('000000', 50);
	}

	public function test_background_shorthand_hex(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(255), $this->equalTo(255), $this->equalTo(255), $this->equalTo(100));

		$image->background('#fff');
	}

	public function test_background_opacity_clamp(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_background')
			->with($this->equalTo(0), $this->equalTo(0), $this->equalTo(0), $this->equalTo(0));

		$image->background('#000', -1);
	}

	public function test_reflection_defaults(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(600), $this->equalTo(100), $this->equalTo(false));

		$image->reflection();
	}

	public function test_reflection_with_height(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(100), $this->equalTo(80), $this->equalTo(true));

		$image->reflection(100, 80, true);
	}

	public function test_reflection_height_clamped(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(600), $this->equalTo(50), $this->equalTo(false));

		$image->reflection(1000, 50);
	}

	public function test_reflection_opacity_clamp(): void
	{
		$image = $this->_createImageMock(800, 600);

		$image->expects($this->once())
			->method('_do_reflection')
			->with($this->equalTo(50), $this->equalTo(0), $this->equalTo(false));

		$image->reflection(50, -5);
	}
}
