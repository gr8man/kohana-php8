<?php

declare(strict_types=1);

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Security extended token and hashing functionality
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.security
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_SecurityHashTest extends Unittest_TestCase
{
	protected string $_original_token_name;

	#[\Override]
	public function setUp(): void
	{
		parent::setUp();
		$this->_original_token_name = Security::$token_name;
	}

	#[\Override]
	public function tearDown(): void
	{
		Security::$token_name = $this->_original_token_name;
		parent::tearDown();
	}

	/**
	 * @test
	 * @covers Kohana_Security::token
	 */
	public function test_token_reuse_when_new_is_false(): void
	{
		$token1 = Security::token(true);
		$token2 = Security::token(false);

		$this->assertSame($token1, $token2);
		Session::instance()->delete(Security::$token_name);
	}

	/**
	 * @test
	 * @covers Kohana_Security::check
	 */
	public function test_check_multiple_validations(): void
	{
		$token = Security::token(true);
		$this->assertTrue(Security::check($token));
		$this->assertTrue(Security::check($token));

		Session::instance()->delete(Security::$token_name);
	}

	/**
	 * @test
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags_with_attributes(): void
	{
		$html = 'Text with <img src="test.jpg" alt="test" /> image';
		$expected = 'Text with test.jpg image';

		$this->assertSame($expected, Security::strip_image_tags($html));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_multiline(): void
	{
		$input = "<?php\necho 'hello';\n?>";
		$expected = "&lt;?php\necho 'hello';\n?&gt;";

		$this->assertSame($expected, Security::encode_php_tags($input));
	}
}
