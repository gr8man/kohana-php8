<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Image_GD driver
 *
 * @package    Kohana/Image
 * @group      kohana
 * @group      kohana.image
 * @group      kohana.image.gd
 * @category   Test
 */
class Kohana_Image_GDTest extends Unittest_TestCase
{
	protected string $test_image;
	protected string $temp_dir;

	public function setUp(): void
	{
		parent::setUp();

		if (! extension_loaded('gd')) {
			$this->markTestSkipped('The GD extension is not available.');
		}

		$this->test_image = MODPATH.'image/tests/test_data/test_image';
		$this->temp_dir = Kohana::$cache_dir.'/image_tests_'.uniqid();
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
		$this->assertTrue(Image_GD::check());
	}

	public function test_resize_and_dimensions(): void
	{
		$image = new Image_GD($this->test_image);
		$orig_width = $image->width;
		$orig_height = $image->height;

		$this->assertGreaterThan(0, $orig_width);
		$this->assertGreaterThan(0, $orig_height);

		$image->resize(50, 50, Image::NONE);
		$this->assertSame(50, $image->width);
		$this->assertSame(50, $image->height);

		$image2 = new Image_GD($this->test_image);
		$image2->resize(40);
		$this->assertSame(40, $image2->width);

		$image3 = new Image_GD($this->test_image);
		$image3->resize(null, 40);
		$this->assertSame(40, $image3->height);

		$image4 = new Image_GD($this->test_image);
		$image4->resize(60, 60, Image::INVERSE);
		$this->assertGreaterThanOrEqual(60, $image4->width);

		$str = (string) $image;
		$this->assertNotEmpty($str);
	}

	public function test_crop(): void
	{
		$image = new Image_GD($this->test_image);
		$image->crop(30, 30, 5, 5);

		$this->assertSame(30, $image->width);
		$this->assertSame(30, $image->height);
	}

	public function test_rotate(): void
	{
		$image = new Image_GD($this->test_image);
		$width = $image->width;
		$height = $image->height;

		$image->rotate(90);
		$this->assertSame($height, $image->width);
		$this->assertSame($width, $image->height);

		$image->rotate(180);
		$this->assertSame($height, $image->width);
		$this->assertSame($width, $image->height);

		$image->rotate(270);
		$this->assertSame($width, $image->width);
		$this->assertSame($height, $image->height);

		$image2 = new Image_GD($this->test_image);
		$image2->rotate(45);
		$this->assertGreaterThan(0, $image2->width);
	}

	public function test_flip(): void
	{
		$image = new Image_GD($this->test_image);
		$image->flip(Image::HORIZONTAL);
		$this->assertGreaterThan(0, $image->width);

		$image->flip(Image::VERTICAL);
		$this->assertGreaterThan(0, $image->height);
	}

	public function test_sharpen(): void
	{
		$image = new Image_GD($this->test_image);
		$image->sharpen(25);
		$this->assertGreaterThan(0, $image->width);
	}

	public function test_reflection(): void
	{
		$image = new Image_GD($this->test_image);
		$orig_height = $image->height;

		$image->reflection(20, 80, true);
		$this->assertSame($orig_height + 20, $image->height);

		$image2 = new Image_GD($this->test_image);
		$image2->reflection(15, 50, false);
		$this->assertSame($orig_height + 15, $image2->height);
	}

	public function test_watermark(): void
	{
		$image = new Image_GD($this->test_image);
		$watermark = new Image_GD($this->test_image);
		$watermark->resize(20, 20);

		$image->watermark($watermark, 5, 5, 50);
		$this->assertGreaterThan(0, $image->width);
	}

	public function test_background(): void
	{
		$image = new Image_GD($this->test_image);
		$image->background('ff0000', 50);
		$this->assertGreaterThan(0, $image->width);

		$image->background('00ff00', 100);
		$this->assertGreaterThan(0, $image->height);
	}

	public function test_render_and_save(): void
	{
		$image = new Image_GD($this->test_image);
		$rendered_png = $image->render('png');
		$this->assertNotEmpty($rendered_png);

		$rendered_jpeg = $image->render('jpeg', 85);
		$this->assertNotEmpty($rendered_jpeg);

		$rendered_gif = $image->render('gif');
		$this->assertNotEmpty($rendered_gif);

		$save_path = $this->temp_dir.'/saved.png';
		$this->assertTrue($image->save($save_path));
		$this->assertFileExists($save_path);

		$saved_image = new Image_GD($save_path);
		$this->assertSame($image->width, $saved_image->width);
		$this->assertSame($image->height, $saved_image->height);
	}
}
