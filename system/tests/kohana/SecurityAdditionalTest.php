<?php

declare(strict_types=1);

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Additional tests for Kohana_Security to improve code coverage.
 */
#[AllowDynamicProperties]
class Kohana_SecurityAdditionalTest extends Unittest_TestCase
{
	/**
	 * @test
	 * @covers Kohana_Security::token
	 * @covers Kohana_Security::check
	 */
	public function test_token_generation_and_check(): void
	{
		// Ensure a fresh token is generated
		$token1 = Security::token(true);
		$this->assertIsString($token1);
		$this->assertNotEmpty($token1);

		// Subsequent call without forcing a new token should return the same value
		$token2 = Security::token(false);
		$this->assertSame($token1, $token2);

		// Verify that the token check works for a valid token
		$this->assertTrue(Security::check($token1));

		// Verify that an invalid token fails the check
		$this->assertFalse(Security::check('invalid-token'));

		// Force a new token and ensure it differs from the previous one
		$token3 = Security::token(true);
		$this->assertIsString($token3);
		$this->assertNotSame($token1, $token3);
		$this->assertTrue(Security::check($token3));
	}

	/**
	 * @test
	 * @covers Kohana_Security::strip_image_tags
	 */
	public function test_strip_image_tags_various_formats(): void
	{
		// Image with double‑quoted src attribute
		$html1 = 'A <img src="image1.png" alt="test" /> B';
		$expected1 = 'A image1.png B';
		$this->assertSame($expected1, Security::strip_image_tags($html1));

		// Image with single‑quoted src attribute
		$html2 = "C <img alt='test' src='image2.jpg'> D";
		$expected2 = 'C image2.jpg D';
		$this->assertSame($expected2, Security::strip_image_tags($html2));

		// Image with src without quotes
		$html3 = 'E <img class="foo" src=image3.gif> F';
		$expected3 = 'E image3.gif F';
		$this->assertSame($expected3, Security::strip_image_tags($html3));

		// Image without a src attribute should be stripped to an empty string
		$html4 = 'G <img alt="no src" /> H';
		$expected4 = 'G  H';
		$this->assertSame($expected4, Security::strip_image_tags($html4));
	}

	/**
	 * @test
	 * @covers Kohana_Security::encode_php_tags
	 */
	public function test_encode_php_tags_multiline_and_single(): void
	{
		$input = "<?php\n echo 'hello';\n?>";
		$expected = "&lt;?php\n echo 'hello';\n?&gt;";
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * @test
	 * @covers Kohana_Security::slow_equals
	 */
	public function test_slow_equals_behaviour(): void
	{
		// When hash_equals is available, slow_equals should delegate to it
		$a = 'same_string';
		$b = 'same_string';
		$this->assertTrue(Security::slow_equals($a, $b));

		$c = 'different';
		$this->assertFalse(Security::slow_equals($a, $c));
	}
}
