<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Image_Imagick driver
 *
 * @package    Kohana/Image
 * @group      kohana
 * @group      kohana.image
 * @group      kohana.image.imagick
 * @category   Test
 */
class Kohana_Image_ImagickTest extends Unittest_TestCase
{
	protected string $test_image;
	protected string $temp_dir;

	public function setUp(): void
	{
		parent::setUp();

		if (! extension_loaded('imagick')) {
			$this->markTestSkipped('The Imagick extension is not available.');
		}

		$this->test_image = MODPATH.'image/tests/test_data/test_image';
		$this->temp_dir = Kohana::$cache_dir.'/image_imagick_tests_'.uniqid();
		if (! is_dir($this->temp_dir)) {
			mkdir($this->temp_dir, 0777, true);
		}
	}

	public function tearDown(): void
	{
		if (is_dir($this->temp_dir)) {
			$files = glob($this->temp_dir.'/*');
			if ($files) {
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
			}
			rmdir($this->temp_dir);
		}
		parent::tearDown();
	}

	public function test_check(): void
	{
		$this->assertTrue(Image_Imagick::check());
	}

	public function test_resize(): void
	{
		$image = new Image_Imagick($this->test_image);
		$image->resize(50, 50);
		$this->assertLessThanOrEqual(50, $image->width);
		$this->assertLessThanOrEqual(50, $image->height);
	}

	public function test_crop(): void
	{
		$image = new Image_Imagick($this->test_image);
		$image->crop(30, 30);
		$this->assertSame(30, $image->width);
		$this->assertSame(30, $image->height);
	}

	public function test_rotate(): void
	{
		$image = new Image_Imagick($this->test_image);
		$orig_w = $image->width;
		$orig_h = $image->height;
		$image->rotate(90);
		$this->assertSame($orig_w, $image->height);
		$this->assertSame($orig_h, $image->width);
	}

	public function test_flip(): void
	{
		$image = new Image_Imagick($this->test_image);
		$this->assertInstanceOf(Image_Imagick::class, $image->flip(Image::HORIZONTAL));
		$this->assertInstanceOf(Image_Imagick::class, $image->flip(Image::VERTICAL));
	}

	public function test_sharpen(): void
	{
		$image = new Image_Imagick($this->test_image);
		$this->assertInstanceOf(Image_Imagick::class, $image->sharpen(20));
	}

	public function test_reflection(): void
	{
		$image = new Image_Imagick($this->test_image);
		$orig_h = $image->height;
		$image->reflection(20, 50, true);
		$this->assertSame($orig_h + 20, $image->height);
	}

	public function test_watermark(): void
	{
		$image = new Image_Imagick($this->test_image);
		$watermark = new Image_Imagick($this->test_image);
		$watermark->resize(20, 20);
		$image->watermark($watermark, 0, 0, 50);
		$this->assertInstanceOf(Image_Imagick::class, $image);
	}

	public function test_background(): void
	{
		$image = new Image_Imagick($this->test_image);
		$image->background('ff0000', 100);
		$this->assertInstanceOf(Image_Imagick::class, $image);
	}

	public function test_render_and_save(): void
	{
		$image = new Image_Imagick($this->test_image);
		$rendered = $image->render('png');
		$this->assertIsString($rendered);
		$this->assertNotEmpty($rendered);

		$save_file = $this->temp_dir.'/saved.png';
		$saved = $image->save($save_file);
		$this->assertTrue($saved);
		$this->assertFileExists($save_file);
	}
}
