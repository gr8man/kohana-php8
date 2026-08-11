<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana upload class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.upload
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_UploadTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_valid()
	 *
	 * @return array
	 */
	public function provider_valid(): array
	{
		$complete = array(
			'error' => UPLOAD_ERR_OK,
			'name' => 'test.txt',
			'type' => 'text/plain',
			'tmp_name' => '/tmp/test.txt',
			'size' => 100,
		);

		return array(
			array(true, $complete),
			array(false, array_diff_key($complete, array('error' => 1))),
			array(false, array_diff_key($complete, array('name' => 1))),
			array(false, array_diff_key($complete, array('type' => 1))),
			array(false, array_diff_key($complete, array('tmp_name' => 1))),
			array(false, array_diff_key($complete, array('size' => 1))),
		);
	}

	/**
	 * Test Upload::valid
	 *
	 * @test
	 * @dataProvider provider_valid
	 * @covers Kohana_Upload::valid
	 */
	public function test_valid(bool $expected, array $file): void
	{
		$this->assertSame($expected, Upload::valid($file));
	}

	/**
	 * Tests Upload::not_empty with missing keys
	 *
	 * @test
	 * @covers Kohana_Upload::not_empty
	 */
	public function test_not_empty_missing_keys(): void
	{
		$this->assertFalse(Upload::not_empty(array()));
		$this->assertFalse(Upload::not_empty(array('error' => UPLOAD_ERR_OK)));
	}

	/**
	 * Tests Upload::not_empty with upload error
	 *
	 * @test
	 * @covers Kohana_Upload::not_empty
	 */
	public function test_not_empty_upload_error(): void
	{
		$this->assertFalse(Upload::not_empty(array(
			'error' => UPLOAD_ERR_NO_FILE,
			'tmp_name' => '/tmp/foo',
		)));
	}

	/**
	 * Tests Upload::not_empty with non-uploaded file
	 *
	 * In CLI is_uploaded_file() always returns false.
	 *
	 * @test
	 * @covers Kohana_Upload::not_empty
	 */
	public function test_not_empty_non_uploaded_file(): void
	{
		$tmp = tmpfile();
		$path = stream_get_meta_data($tmp)['uri'];
		$this->assertFalse(Upload::not_empty(array(
			'error' => UPLOAD_ERR_OK,
			'tmp_name' => $path,
		)));
		fclose($tmp);
	}

	/**
	 * Provides test data for test_type()
	 *
	 * @return array
	 */
	public function provider_type(): array
	{
		return array(
			// Matching extension
			array(
				array('error' => UPLOAD_ERR_OK, 'name' => 'test.png'),
				array('jpg', 'png', 'gif'),
				true,
			),
			// Non-matching extension
			array(
				array('error' => UPLOAD_ERR_OK, 'name' => 'test.png'),
				array('docx'),
				false,
			),
			// Case-insensitive
			array(
				array('error' => UPLOAD_ERR_OK, 'name' => 'test.PNG'),
				array('jpg', 'png', 'gif'),
				true,
			),
			// Upload error returns true regardless of extension
			array(
				array('error' => UPLOAD_ERR_NO_FILE, 'name' => 'test.png'),
				array('jpg'),
				true,
			),
			// No extension
			array(
				array('error' => UPLOAD_ERR_OK, 'name' => 'test'),
				array('jpg'),
				false,
			),
			// Multiple dots in filename
			array(
				array('error' => UPLOAD_ERR_OK, 'name' => 'test.file.png'),
				array('png'),
				true,
			),
		);
	}

	/**
	 * Tests Upload::type
	 *
	 * @test
	 * @dataProvider provider_type
	 * @covers Kohana_Upload::type
	 */
	public function test_type(array $file, array $allowed, bool $expected): void
	{
		$this->assertSame($expected, Upload::type($file, $allowed));
	}

	/**
	 * Provides test data for test_size()
	 *
	 * @return array
	 */
	public function provider_size(): array
	{
		return array(
			// UPLOAD_ERR_INI_SIZE returns false
			array(
				array('error' => UPLOAD_ERR_INI_SIZE, 'size' => 5000),
				'10K',
				false,
			),
			// Other errors return true
			array(
				array('error' => UPLOAD_ERR_NO_FILE, 'size' => 5000),
				'10K',
				true,
			),
			// Valid size within limit
			array(
				array('error' => UPLOAD_ERR_OK, 'size' => 5000),
				'10K',
				true,
			),
			// Exceeded size
			array(
				array('error' => UPLOAD_ERR_OK, 'size' => 15000),
				'10K',
				false,
			),
			// Equal size (boundary)
			array(
				array('error' => UPLOAD_ERR_OK, 'size' => 10240),
				'10K',
				true,
			),
		);
	}

	/**
	 * Tests Upload::size
	 *
	 * @test
	 * @dataProvider provider_size
	 * @covers Kohana_Upload::size
	 */
	public function test_size(array $file, string $size, bool $expected): void
	{
		$this->assertSame($expected, Upload::size($file, $size));
	}

	/**
	 * size() should throw an exception if the supplied max size is invalid
	 *
	 * @test
	 * @covers Kohana_Upload::size
	 */
	public function test_size_throws_exception_for_invalid_size(): void
	{
		$this->expectException(Kohana_Exception::class);

		Upload::size(array(
			'error' => UPLOAD_ERR_OK,
			'size' => 100,
		), '1DooDah');
	}

	/**
	 * Tests Upload::image with various inputs
	 *
	 * In CLI, is_uploaded_file() always returns false,
	 * so image() should return false for all these cases.
	 *
	 * @test
	 * @covers Kohana_Upload::image
	 */
	public function test_image_returns_false(): void
	{
		// Empty array
		$this->assertFalse(Upload::image(array()));

		// Missing error key
		$this->assertFalse(Upload::image(array('tmp_name' => '/tmp/foo')));

		// Missing tmp_name key
		$this->assertFalse(Upload::image(array('error' => UPLOAD_ERR_OK)));

		// Upload error
		$this->assertFalse(Upload::image(array(
			'error' => UPLOAD_ERR_NO_FILE,
			'tmp_name' => '/tmp/foo',
		)));

		// Non-uploaded file (is_uploaded_file fails in CLI)
		$tmp = tmpfile();
		$path = stream_get_meta_data($tmp)['uri'];
		$this->assertFalse(Upload::image(array(
			'error' => UPLOAD_ERR_OK,
			'tmp_name' => $path,
		)));
		fclose($tmp);

		// With width/height params
		$this->assertFalse(Upload::image(array(), 100, 100));
		$this->assertFalse(Upload::image(array(), 100, 100, true));
	}
}
