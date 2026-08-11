<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana_Security
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.security
 *
 * @package    Kohana
 * @category   Tests
 */
#[AllowDynamicProperties]
class Kohana_SecurityTest extends Unittest_TestCase
{
	public function provider_encode_php_tags()
	{
		return array(
			array("&lt;?php echo 'helloo'; ?&gt;", "<?php echo 'helloo'; ?>"),
			array("&lt;? echo 'short'; ?&gt;", "<? echo 'short'; ?>"),
			array("no tags", "no tags"),
			array("", ""),
			array("&lt;?= \$var ?&gt;", "<?= \$var ?>"),
			array("&lt;?php ?&gt;", "<?php ?>"),
		);
	}

	/**
	 * @test
	 * @dataProvider provider_encode_php_tags
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags($expected, $input)
	{
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_no_tags()
	{
		$input = 'plain text without any php tags';
		$this->assertSame($input, Security::encode_php_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_full_tag()
	{
		$input = "<?php echo 'test'; ?>";
		$expected = "&lt;?php echo 'test'; ?&gt;";
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_short_tag()
	{
		$input = "<? echo 'short'; ?>";
		$expected = "&lt;? echo 'short'; ?&gt;";
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_echo_tag()
	{
		$input = "<?= \$var ?>";
		$expected = "&lt;?= \$var ?&gt;";
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	public function provider_strip_image_tags()
	{
		return array(
			array('foo', '<img src="foo" />'),
			array('https://example.com/image.png', '<img src="https://example.com/image.png" />'),
			array('/images/photo.jpg', '<img src="/images/photo.jpg" alt="photo">'),
			array('test', '<img src=test>'),
			array('<img>', '<img>'),
			array('', '<img alt="no src">'),
		);
	}

	/**
	 * @test
	 * @dataProvider provider_strip_image_tags
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags($expected, $input)
	{
		$this->assertSame($expected, Security::strip_image_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags_multiple()
	{
		$input = '<img src="first.png" /> text <img src="second.jpg" />';
		$result = Security::strip_image_tags($input);
		$this->assertStringContainsString('first.png', $result);
		$this->assertStringContainsString('second.jpg', $result);
		$this->assertStringContainsString('text', $result);
	}

	/**
	 * @test
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags_no_src()
	{
		$this->assertSame('', Security::strip_image_tags('<img alt="no src" />'));
	}

	/**
	 * @test
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags_no_tag()
	{
		$input = 'just plain text';
		$this->assertSame($input, Security::strip_image_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::token
	 */
	public function test_token_returns_non_empty_string()
	{
		$token = Security::token(true);
		$this->assertNotEmpty($token);
		$this->assertInternalType('string', $token);
		Session::instance()->delete(Security::$token_name);
	}

	/**
	 * @test
	 * @covers Kohana_Security::token
	 */
	public function test_token_multiple_calls_return_different_tokens()
	{
		$token1 = Security::token(true);
		$token2 = Security::token(true);
		$this->assertNotSame($token1, $token2);
		Session::instance()->delete(Security::$token_name);
	}

	/**
	 * @test
	 * @covers Kohana_Security::check
	 */
	public function test_check_returns_true_for_valid_token(): void
	{
		$token = Security::token(true);
		$this->assertTrue(Security::check($token));
		Session::instance()->delete(Security::$token_name);
	}

	/**
	 * @test
	 * @covers Kohana_Security::check
	 */
	public function test_check_returns_false_for_invalid_token(): void
	{
		$this->assertFalse(Security::check('invalid_token_value'));
	}

	/**
	 * @test
	 * @covers Kohana_Security::check
	 */
	public function test_check_returns_false_for_empty_token(): void
	{
		$this->assertFalse(Security::check(''));
	}

	/**
	 * @test
	 * @covers Kohana_Security::token
	 * @covers Kohana_Security::check
	 */
	public function test_token_and_check_multiple_names(): void
	{
		for ($i = 0; $i <= 4; $i++) {
			Security::$token_name = 'token_multi_'.$i;
			$token = Security::token(true);
			$this->assertTrue(Security::check($token));
			$this->assertSame($token, Security::token(false));
			Session::instance()->delete(Security::$token_name);
		}
	}

	public function provider_slow_equals(): array
	{
		return array(
			array(true, 'abc', 'abc'),
			array(true, '', ''),
			array(true, '1234567890', '1234567890'),
			array(false, 'abc', 'abd'),
			array(false, 'abc', 'abcd'),
			array(false, 'abc', 'ab'),
			array(false, 'ABC', 'abc'),
			array(false, '', 'a'),
		);
	}

	/**
	 * @test
	 * @dataProvider provider_slow_equals
	 * @covers Kohana_Security::slow_equals
	 */
	public function test_slow_equals(bool $expected, string $a, string $b): void
	{
		$this->assertSame($expected, Security::slow_equals($a, $b));
	}

	/**
	 * @test
	 * @covers Kohana_Security::slow_equals
	 */
	public function test_slow_equals_long_strings(): void
	{
		$a = str_repeat('x', 1000);
		$b = str_repeat('x', 1000);
		$this->assertTrue(Security::slow_equals($a, $b));
	}

	/**
	 * @test
	 * @covers Kohana_Security::slow_equals
	 */
	public function test_slow_equals_long_mismatch(): void
	{
		$a = str_repeat('x', 1000);
		$b = str_repeat('y', 1000);
		$this->assertFalse(Security::slow_equals($a, $b));
	}

	/**
	 * @test
	 * @covers Kohana_Security::slow_equals
	 */
	public function test_slow_equals_different_lengths(): void
	{
		$this->assertFalse(Security::slow_equals('short', 'longer string here'));
	}
}
