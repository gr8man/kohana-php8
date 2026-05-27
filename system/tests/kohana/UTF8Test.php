<?php

declare(strict_types=1); defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');
/**
 * Tests Kohana_UTF8 class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.utf8
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
#[AllowDynamicProperties]
class Kohana_UTF8Test extends Unittest_TestCase
{

	/**
	 * Provides test data for test_clean()
	 */
	public function provider_clean()
	{
		return array(
			array("\0", ''),
			array("→foo\021", '→foo'),
			array("\x7Fbar", 'bar'),
			array("\xFF", ''),
			array("\x41", 'A'),
			array(array("→foo\021", "\x41"), array('→foo', 'A')),
		);
	}

	/**
	 * Tests UTF8::clean
	 *
	 * @test
	 * @dataProvider provider_clean
	 */
	public function test_clean($input, $expected)
	{
		$this->assertSame($expected, UTF8::clean($input));
	}

	/**
	 * Provides test data for test_is_ascii()
	 */
	public function provider_is_ascii()
	{
		return array(
			array("\0", TRUE),
			array("\$eno\r", TRUE),
			array('Señor', FALSE),
			array(array('Se', 'nor'), TRUE),
			array(array('Se', 'ñor'), FALSE),
		);
	}

	/**
	 * Tests UTF8::is_ascii
	 *
	 * @test
	 * @dataProvider provider_is_ascii
	 */
	public function test_is_ascii($input, $expected)
	{
		$this->assertSame($expected, UTF8::is_ascii($input));
	}

	/**
	 * Provides test data for test_strip_ascii_ctrl()
	 */
	public function provider_strip_ascii_ctrl()
	{
		return array(
			array("\0", ''),
			array("→foo\021", '→foo'),
			array("\x7Fbar", 'bar'),
			array("\xFF", "\xFF"),
			array("\x41", 'A'),
		);
	}

	/**
	 * Tests UTF8::strip_ascii_ctrl
	 *
	 * @test
	 * @dataProvider provider_strip_ascii_ctrl
	 */
	public function test_strip_ascii_ctrl($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_ascii_ctrl($input));
	}

	/**
	 * Provides test data for test_strip_non_ascii()
	 */
	public function provider_strip_non_ascii()
	{
		return array(
			array("\0\021\x7F", "\0\021\x7F"),
			array('I ♥ cocoñùт', 'I  coco'),
		);
	}

	/**
	 * Tests UTF8::strip_non_ascii
	 *
	 * @test
	 * @dataProvider provider_strip_non_ascii
	 */
	public function test_strip_non_ascii($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_non_ascii($input));
	}

	/**
	 * Provides test data for test_transliterate_to_ascii()
	 */
	public function provider_transliterate_to_ascii()
	{
		return array(
			array('Cocoñùт', -1, 'Coconuт'),
			array('COCOÑÙТ', -1, 'COCOÑÙТ'),
			array('Cocoñùт', 0, 'Coconuт'),
			array('COCOÑÙТ', 0, 'COCONUТ'),
			array('Cocoñùт', 1, 'Cocoñùт'),
			array('COCOÑÙТ', 1, 'COCONUТ'),
		);
	}

	/**
	 * Tests UTF8::transliterate_to_ascii
	 *
	 * @test
	 * @dataProvider provider_transliterate_to_ascii
	 */
	public function test_transliterate_to_ascii($input, $case, $expected)
	{
		$this->assertSame($expected, UTF8::transliterate_to_ascii($input, $case));
	}

	/**
	 * Provides test data for test_strlen()
	 */
	public function provider_strlen()
	{
		return array(
			array('Cocoñùт', 7),
			array('Coconut', 7),
		);
	}

	/**
	 * Tests UTF8::strlen
	 *
	 * @test
	 * @dataProvider provider_strlen
	 */
	public function test_strlen($input, $expected)
	{
		$this->assertSame($expected, UTF8::strlen($input));
	}

	/**
	 * Provides test data for test_strpos()
	 */
	public function provider_strpos()
	{
		return array(
			array('Cocoñùт', 'o', 0, 1),
			array('Cocoñùт', 'ñ', 1, 4),
		);
	}

	/**
	 * Tests UTF8::strpos
	 *
	 * @test
	 * @dataProvider provider_strpos
	 */
	public function test_strpos($input, $str, $offset, $expected)
	{
		$this->assertSame($expected, UTF8::strpos($input, $str, $offset));
	}

	/**
	 * Provides test data for test_strrpos()
	 */
	public function provider_strrpos()
	{
		return array(
			array('Cocoñùт', 'o', 0, 3),
			array('Cocoñùт', 'ñ', 2, 4),
		);
	}

	/**
	 * Tests UTF8::strrpos
	 *
	 * @test
	 * @dataProvider provider_strrpos
	 */
	public function test_strrpos($input, $str, $offset, $expected)
	{
		$this->assertSame($expected, UTF8::strrpos($input, $str, $offset));
	}

	/**
	 * Provides test data for test_substr()
	 */
	public function provider_substr()
	{
		return array(
			array('Cocoñùт', 3, 2, 'oñ'),
			array('Cocoñùт', 3, 9, 'oñùт'),
			array('Cocoñùт', 3, NULL, 'oñùт'),
			array('Cocoñùт', 3, -2, 'oñ'),
		);
	}

	/**
	 * Tests UTF8::substr
	 *
	 * @test
	 * @dataProvider provider_substr
	 */
	public function test_substr($input, $offset, $length, $expected)
	{
		$this->assertSame($expected, UTF8::substr($input, $offset, $length));
	}

	/**
	 * Provides test data for test_substr_replace()
	 */
	public function provider_substr_replace()
	{
		return array(
			array('Cocoñùт', 'šš', 3, 2, 'Cocššùт'),
			array('Cocoñùт', 'šš', 3, 9, 'Cocšš'),
		);
	}

	/**
	 * Tests UTF8::substr_replace
	 *
	 * @test
	 * @dataProvider provider_substr_replace
	 */
	public function test_substr_replace($input, $replacement, $offset, $length, $expected)
	{
		$this->assertSame($expected, UTF8::substr_replace($input, $replacement, $offset, $length));
	}

	/**
	 * Provides test data for test_strtolower()
	 */
	public function provider_strtolower()
	{
		return array(
			array('COCOÑÙТ', 'cocoñùт'),
			array('JÄGER',   'jäger'),
		);
	}

	/**
	 * Tests UTF8::strtolower
	 *
	 * @test
	 * @dataProvider provider_strtolower
	 */
	public function test_strtolower($input, $expected)
	{
		$this->assertSame($expected, UTF8::strtolower($input));
	}

	/**
	 * Provides test data for test_strtoupper()
	 */
	public function provider_strtoupper()
	{
		return array(
			array('Cocoñùт', 'COCOÑÙТ'),
			array('jäger',   'JÄGER'),
		);
	}

	/**
	 * Tests UTF8::strtoupper
	 *
	 * @test
	 * @dataProvider provider_strtoupper
	 */
	public function test_strtoupper($input, $expected)
	{
		$this->assertSame($expected, UTF8::strtoupper($input));
	}

	/**
	 * Provides test data for test_ucfirst()
	 */
	public function provider_ucfirst()
	{
		return array(
			array('ñùт', 'Ñùт'),
		);
	}

	/**
	 * Tests UTF8::ucfirst
	 *
	 * @test
	 * @dataProvider provider_ucfirst
	 */
	public function test_ucfirst($input, $expected)
	{
		$this->assertSame($expected, UTF8::ucfirst($input));
	}

	/**
	 * Provides test data for test_strip_non_ascii()
	 */
	public function provider_ucwords()
	{
		return array(
			array('ExAmple', 'ExAmple'),
			array('i ♥ Cocoñùт', 'I ♥ Cocoñùт'),
		);
	}

	/**
	 * Tests UTF8::ucwords
	 *
	 * @test
	 * @dataProvider provider_ucwords
	 */
	public function test_ucwords($input, $expected)
	{
		$this->assertSame($expected, UTF8::ucwords($input));
	}

	/**
	 * Provides test data for test_strcasecmp()
	 */
	public function provider_strcasecmp()
	{
		return array(
			array('Cocoñùт',   'Cocoñùт', 0, 0),
			array('Čau',       'Čauo',   -1, -1),
			array('Čau',       'Ča',      1, 1),
			array('Cocoñùт',   'Cocoñ',   4, 1),
			array('Cocoñùт',   'Coco',    6, 1),
		);
	}

	/**
	 * Tests UTF8::strcasecmp
	 *
	 * @test
	 * @dataProvider provider_strcasecmp
	 */
	public function test_strcasecmp($input, $input2, $expected_old, $expected_sign)
	{
		$result = UTF8::strcasecmp($input, $input2);

		if ($expected_sign === 0)
		{
			$this->assertSame(0, $result);
		}
		elseif ($expected_sign > 0)
		{
			$this->assertGreaterThan(0, $result);
		}
		else
		{
			$this->assertLessThan(0, $result);
		}
	}

	/**
	 * Provides test data for test_str_ireplace()
	 */
	public function provider_str_ireplace()
	{
		return array(
			array('т', 't', 'cocoñuт', 'cocoñut'),
			array('Ñ', 'N', 'cocoñuт', 'cocoNuт'),
			array(array('т', 'Ñ', 'k' => 'k'), array('t', 'N', 'K'), array('cocoñuт'), array('cocoNut')),
			array(array('ñ'), 'n', 'cocoñuт', 'coconuт'),
		);
	}

	/**
	 * Tests UTF8::str_ireplace
	 *
	 * @test
	 * @dataProvider provider_str_ireplace
	 */
	public function test_str_ireplace($search, $replace, $subject, $expected)
	{
		$this->assertSame($expected, UTF8::str_ireplace($search, $replace, $subject));
	}

	/**
	 * Provides test data for test_stristr()
	 */
	public function provider_stristr()
	{
		return array(
			array('Cocoñùт',   'oñ', 'oñùт'),
			array('Cocoñùт',   'o', 'ocoñùт'),
			array('Cocoñùт',   'k', FALSE),
		);
	}

	/**
	 * Tests UTF8::stristr
	 *
	 * @test
	 * @dataProvider provider_stristr
	 */
	public function test_stristr($input, $input2, $expected)
	{
		$this->assertSame($expected, UTF8::stristr($input, $input2));
	}

	/**
	 * Provides test data for test_strspn()
	 */
	public function provider_strspn()
	{
		return array(
			array("foo", "o", 1, 2, 2),
			array('Cocoñùт', 'oñ', NULL, NULL, 1),
			array('Cocoñùт', 'oñ', 2, 4, 1),
			array('Cocoñùт', 'šš', 3, 9, 4),
		);
	}

	/**
	 * Tests UTF8::strspn
	 *
	 * @test
	 * @dataProvider provider_strspn
	 */
	public function test_strspn($input, $mask, $offset, $length, $expected)
	{
		$this->assertSame($expected, UTF8::strspn($input, $mask, $offset, $length));
	}

	/**
	 * Provides test data for test_strcspn()
	 */
	public function provider_strcspn()
	{
		return array(
			array('Cocoñùт', 'oñ', NULL, NULL, 1),
			array('Cocoñùт', 'oñ', 2, 4, 1),
			array('Cocoñùт', 'šš', 3, 9, 4),
		);
	}

	/**
	 * Tests UTF8::strcspn
	 *
	 * @test
	 * @dataProvider provider_strcspn
	 */
	public function test_strcspn($input, $mask, $offset, $length, $expected)
	{
		$this->assertSame($expected, UTF8::strcspn($input, $mask, $offset, $length));
	}

	/**
	 * Provides test data for test_str_pad()
	 */
	public function provider_str_pad()
	{
		return array(
			array('Cocoñùт', 10, 'š', STR_PAD_RIGHT, 'Cocoñùтššš'),
			array('Cocoñùт', 10, 'š', STR_PAD_LEFT,  'šššCocoñùт'),
			array('Cocoñùт', 10, 'š', STR_PAD_BOTH,  'šCocoñùтšš'),
		);
	}

	/**
	 * Tests UTF8::str_pad
	 *
	 * @test
	 * @dataProvider provider_str_pad
	 */
	public function test_str_pad($input, $length, $pad, $type, $expected)
	{
		$this->assertSame($expected, UTF8::str_pad($input, $length, $pad, $type));
	}

        /**
	 * Tests UTF8::str_pad error
	 *
	 * @test
	 */
	public function test_str_pad_error()
	{
		$this->expectException('UTF8_Exception');
		UTF8::str_pad('Cocoñùт', 10, 'š', 15,  'šCocoñùтšš');
	}

	/**
	 * Provides test data for test_str_split()
	 */
	public function provider_str_split()
	{
		return array(
			array('Bár',     1, array('B', 'á', 'r')),
			array('Cocoñùт', 2, array('Co', 'co', 'ñù', 'т')),
			array('Cocoñùт', 3, array('Coc', 'oñù', 'т')),
		);
	}

	/**
	 * Tests UTF8::str_split
	 *
	 * @test
	 * @dataProvider provider_str_split
	 */
	public function test_str_split($input, $split_length, $expected)
	{
		$this->assertSame($expected, UTF8::str_split($input, $split_length));
	}

	/**
	 * Provides test data for test_strrev()
	 */
	public function provider_strrev()
	{
		return array(
			array('Cocoñùт', 'тùñocoC'),
		);
	}

	/**
	 * Tests UTF8::strrev
	 *
	 * @test
	 * @dataProvider provider_strrev
	 */
	public function test_strrev($input, $expected)
	{
		$this->assertSame($expected, UTF8::strrev($input));
	}

	/**
	 * Provides test data for test_trim()
	 */
	public function provider_trim()
	{
		return array(
			array(' bar ', NULL, 'bar'),
			array('bar',   'b',  'ar'),
			array('barb',  'b',  'ar'),
		);
	}

	/**
	 * Tests UTF8::trim
	 *
	 * @test
	 * @dataProvider provider_trim
	 */
	public function test_trim($input, $input2, $expected)
	{
		$this->assertSame($expected, UTF8::trim($input, $input2));
	}

	/**
	 * Provides test data for test_ltrim()
	 */
	public function provider_ltrim()
	{
		return array(
			array(' bar ', NULL, 'bar '),
			array('bar',   'b',  'ar'),
			array('barb',  'b',  'arb'),
			array('ñùт',   'ñ',  'ùт'),
		);
	}

	/**
	 * Tests UTF8::ltrim
	 *
	 * @test
	 * @dataProvider provider_ltrim
	 */
	public function test_ltrim($input, $charlist, $expected)
	{
		$this->assertSame($expected, UTF8::ltrim($input, $charlist));
	}

	/**
	 * Provides test data for test_rtrim()
	 */
	public function provider_rtrim()
	{
		return array(
			array(' bar ', NULL, ' bar'),
			array('bar',   'b',  'bar'),
			array('barb',  'b',  'bar'),
			array('Cocoñùт',  'т',  'Cocoñù'),
		);
	}

	/**
	 * Tests UTF8::rtrim
	 *
	 * @test
	 * @dataProvider provider_rtrim
	 */
	public function test_rtrim($input, $input2, $expected)
	{
		$this->assertSame($expected, UTF8::rtrim($input, $input2));
	}

	/**
	 * Provides test data for test_ord()
	 */
	public function provider_ord()
	{
		return array(
			array('f', 102),
			array('ñ', 241),
			array('Ñ', 209),
		);
	}

	/**
	 * Tests UTF8::ord
	 *
	 * @test
	 * @dataProvider provider_ord
	 */
	public function test_ord($input, $expected)
	{
		$this->assertSame($expected, UTF8::ord($input));
	}

	/**
	 * Tests UTF8::strpos with empty string
	 *
	 * @test
	 */
	public function test_strpos_empty_needle()
	{
		$this->assertSame(0, UTF8::strpos('hello', ''));
	}

	/**
	 * Tests UTF8::strpos with non-existent needle
	 *
	 * @test
	 */
	public function test_strpos_not_found()
	{
		$this->assertFalse(UTF8::strpos('hello', 'x'));
	}

	/**
	 * Tests UTF8::strrpos with offset
	 *
	 * @test
	 */
	public function test_strrpos_with_offset()
	{
		$this->assertSame(7, UTF8::strrpos('hello world', 'o', 3));
	}

	/**
	 * Tests UTF8::strrpos not found
	 *
	 * @test
	 */
	public function test_strrpos_not_found()
	{
		$this->assertFalse(UTF8::strrpos('hello', 'x'));
	}

	/**
	 * Tests UTF8::substr with negative offset
	 *
	 * @test
	 */
	public function test_substr_negative_offset()
	{
		$this->assertSame('rld', UTF8::substr('hello world', -3));
	}

	/**
	 * Tests UTF8::substr with negative length
	 *
	 * @test
	 */
	public function test_substr_negative_length()
	{
		$this->assertSame('worl', UTF8::substr('hello world', 6, -1));
	}

	/**
	 * Tests UTF8::substr empty result
	 *
	 * @test
	 */
	public function test_substr_empty_result()
	{
		$this->assertSame('', UTF8::substr('hi', 5));
	}

	/**
	 * Tests UTF8::substr whole string when offset is 0
	 *
	 * @test
	 */
	public function test_substr_whole_string()
	{
		$this->assertSame('hello', UTF8::substr('hello', 0));
	}

	/**
	 * Tests UTF8::str_split with multi-byte characters
	 *
	 * @test
	 */
	public function test_str_split_multi_byte()
	{
		$result = UTF8::str_split('ñoño');
		$this->assertCount(4, $result);
		$this->assertSame('ñ', $result[0]);
	}

	/**
	 * Tests UTF8::str_split with custom split length
	 *
	 * @test
	 */
	public function test_str_split_custom_length()
	{
		$result = UTF8::str_split('hello', 2);
		$this->assertCount(3, $result);
		$this->assertSame('he', $result[0]);
	}

	/**
	 * Tests UTF8::strrev with multibyte
	 *
	 * @test
	 */
	public function test_strrev_multi_byte()
	{
		$this->assertSame('óññó', UTF8::strrev('óññó'));
	}

	/**
	 * Tests UTF8::strrev with empty string
	 *
	 * @test
	 */
	public function test_strrev_empty()
	{
		$this->assertSame('', UTF8::strrev(''));
	}

	/**
	 * Tests UTF8::str_pad with UTF-8 string
	 *
	 * @test
	 */
	public function test_str_pad_utf8()
	{
		$result = UTF8::str_pad('ñ', 5, '_');
		$this->assertSame('ñ____', $result);
	}

	/**
	 * Tests UTF8::str_pad STR_PAD_BOTH
	 *
	 * @test
	 */
	public function test_str_pad_both()
	{
		$result = UTF8::str_pad('hello', 9, '-', STR_PAD_BOTH);
		$this->assertSame('--hello--', $result);
	}

	/**
	 * Tests UTF8::str_pad with invalid type throws exception
	 *
	 * @test
	 */
	public function test_str_pad_invalid_type()
	{
		$this->expectException('ValueError');
		UTF8::str_pad('test', 10, ' ', 999);
	}

	/**
	 * Tests UTF8::str_pad when string is longer than target
	 *
	 * @test
	 */
	public function test_str_pad_shorter_than_string()
	{
		$this->assertSame('hello', UTF8::str_pad('hello', 3));
	}

	/**
	 * Tests UTF8::strlen with empty string
	 *
	 * @test
	 */
	public function test_strlen_empty()
	{
		$this->assertSame(0, UTF8::strlen(''));
	}

	/**
	 * Tests UTF8::strlen with multi-byte
	 *
	 * @test
	 */
	public function test_strlen_multibyte()
	{
		$this->assertSame(4, UTF8::strlen('ñoña'));
	}

	/**
	 * Tests UTF8::str_ireplace
	 *
	 * @test
	 */
	public function test_str_ireplace_multibyte()
	{
		$result = UTF8::str_ireplace('ÑO', 'SI', 'HOLA ÑO');
		$this->assertSame('HOLA SI', $result);
	}

	/**
	 * Tests UTF8::stristr with multibyte
	 *
	 * @test
	 */
	public function test_stristr_multibyte()
	{
		$result = UTF8::stristr('HOLA ño', 'ÑO');
		$this->assertSame('ño', $result);
	}

	/**
	 * Tests UTF8::stristr not found
	 *
	 * @test
	 */
	public function test_stristr_not_found()
	{
		$this->assertFalse(UTF8::stristr('hello', 'x'));
	}

	/**
	 * Tests UTF8::strspn
	 *
	 * @test
	 */
	public function test_strspn_basic()
	{
		$this->assertSame(3, UTF8::strspn('aaabc', 'a'));
	}

	/**
	 * Tests UTF8::strcspn
	 *
	 * @test
	 */
	public function test_strcspn_basic()
	{
		$this->assertSame(2, UTF8::strcspn('abcde', 'c'));
	}

	/**
	 * Tests UTF8::substr_replace
	 *
	 * @test
	 */
	public function test_substr_replace_basic()
	{
		$this->assertSame('hilo world', UTF8::substr_replace('hello world', 'hi', 0, 3));
	}

	/**
	 * Tests UTF8::ucfirst
	 *
	 * @test
	 */
	public function test_ucfirst_multibyte()
	{
		$this->assertSame('Ñoña', UTF8::ucfirst('ñoña'));
	}

	/**
	 * Tests UTF8::ucwords
	 *
	 * @test
	 */
	public function test_ucwords_multibyte()
	{
		$this->assertSame('Ño Ño', UTF8::ucwords('ño ño'));
	}

	/**
	 * Tests UTF8::clean with valid UTF-8
	 *
	 * @test
	 */
	public function test_clean_valid_utf8()
	{
		$this->assertSame('ñoño', UTF8::clean('ñoño'));
	}

	/**
	 * Tests UTF8::clean with invalid UTF-8
	 *
	 * @test
	 */
	public function test_clean_invalid_utf8()
	{
		$invalid = "abc\x80\xFE\xFFdef";
		$result = UTF8::clean($invalid);
		$this->assertStringNotContainsString("\x80", $result);
	}

	/**
	 * Tests UTF8::is_ascii with ascii string
	 *
	 * @test
	 */
	public function test_is_ascii_true()
	{
		$this->assertTrue(UTF8::is_ascii('hello'));
	}

	/**
	 * Tests UTF8::is_ascii with non-ascii string
	 *
	 * @test
	 */
	public function test_is_ascii_false()
	{
		$this->assertFalse(UTF8::is_ascii('ño'));
	}

	/**
	 * Tests UTF8::is_ascii with empty string
	 *
	 * @test
	 */
	public function test_is_ascii_empty()
	{
		$this->assertTrue(UTF8::is_ascii(''));
	}

	/**
	 * Tests UTF8::strcasecmp multi-byte
	 *
	 * @test
	 */
	public function test_strcasecmp_multibyte()
	{
		$this->assertEquals(0, UTF8::strcasecmp('ñ', 'Ñ'));
	}

	/**
	 * Tests UTF8::strtolower with multi-byte
	 *
	 * @test
	 */
	public function test_strtolower_multibyte()
	{
		$this->assertSame('ño', UTF8::strtolower('ÑO'));
	}

	/**
	 * Tests UTF8::strtoupper with multi-byte
	 *
	 * @test
	 */
	public function test_strtoupper_multibyte()
	{
		$this->assertSame('ÑO', UTF8::strtoupper('ño'));
	}

	/**
	 * Tests UTF8::trim
	 *
	 * @test
	 */
	public function test_trim_multibyte()
	{
		$this->assertSame('ño', UTF8::trim(' ño '));
	}

	/**
	 * Tests UTF8::ltrim
	 *
	 * @test
	 */
	public function test_ltrim_multibyte()
	{
		$this->assertSame('ño ', UTF8::ltrim(' ño '));
	}

	/**
	 * Tests UTF8::rtrim
	 *
	 * @test
	 */
	public function test_rtrim_multibyte()
	{
		$this->assertSame(' ño', UTF8::rtrim(' ño '));
	}

	/**
	 * Tests UTF8::transliterate_to_ascii
	 *
	 * @test
	 */
	public function test_transliterate_to_ascii_simple()
	{
		$result = UTF8::transliterate_to_ascii('ñöü');
		$this->assertSame('nou', $result);
	}

	/**
	 * Tests UTF8::strip_ascii_ctrl
	 *
	 * @test
	 */
	public function test_strip_ascii_ctrl_simple()
	{
		$result = UTF8::strip_ascii_ctrl("Hello\x00World\x1F");
		$this->assertSame('HelloWorld', $result);
	}

	/**
	 * Tests UTF8::strip_non_ascii
	 *
	 * @test
	 */
	public function test_strip_non_ascii_simple()
	{
		$result = UTF8::strip_non_ascii('I ♥ cocoñùт');
		$this->assertSame('I  coco', $result);
	}
}
