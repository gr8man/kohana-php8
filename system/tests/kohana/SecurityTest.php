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
	/**
	 * Provides test data for test_envode_php_tags()
	 *
	 * @return array Test data sets
	 */
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
	 * Tests Security::encode_php_tags()
	 *
	 * @test
	 * @dataProvider provider_encode_php_tags
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags($expected, $input)
	{
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * Provides test data for test_strip_image_tags()
	 *
	 * @return array Test data sets
	 */
	public function provider_strip_image_tags()
	{
		return array(
			array('foo', '<img src="foo" />'),
			array('https://example.com/image.png', '<img src="https://example.com/image.png" />'),
			array('/images/photo.jpg', '<img src="/images/photo.jpg" alt="photo">'),
			array('test', '<img src=test>'),
			array('', '<img>'),
			array('', '<img alt="no src">'),
		);
	}

	/**
	 * Tests Security::strip_image_tags()
	 *
	 * @test
	 * @dataProvider provider_strip_image_tags
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags($expected, $input)
	{
		$this->assertSame($expected, Security::strip_image_tags($input));
	}

	/**
	 * Provides test data for Security::token()
	 *
	 * @return array Test data sets
	 */
	public function provider_csrf_token()
	{
		$array = array();
		for ($i = 0; $i <= 4; $i++) {
			Security::$token_name = 'token_'.$i;
			$array[] = array(Security::token(true), Security::check(Security::token(false)), $i);
		}
		return $array;
	}

	/**
	 * Tests Security::token()
	 *
	 * @test
	 * @dataProvider provider_csrf_token
	 * @covers Kohana_Security::token
	 */
	public function test_csrf_token($expected, $input, $iteration)
	{
		//@todo: the Security::token tests need to be reviewed to check how much of the logic they're actually covering
		Security::$token_name = 'token_'.$iteration;
		$this->assertSame(true, $input);
		$this->assertSame($expected, Security::token(false));
		Session::instance()->delete(Security::$token_name);
	}

	public function test_check_returns_true_for_valid_token(): void
	{
		$token = Security::token(true);
		$this->assertTrue(Security::check($token));
		Session::instance()->delete(Security::$token_name);
	}

	public function test_check_returns_false_for_invalid_token(): void
	{
		$this->assertFalse(Security::check('invalid_token_value'));
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
	 * @dataProvider provider_slow_equals
	 */
	public function test_slow_equals(bool $expected, string $a, string $b): void
	{
		$this->assertSame($expected, Security::slow_equals($a, $b));
	}
}
