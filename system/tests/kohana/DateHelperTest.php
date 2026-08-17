<?php

declare(strict_types=1);

defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kohana_Date helper methods.
 */
#[AllowDynamicProperties]
class DateHelperTest extends Unittest_TestCase
{
	/** @test @covers Kohana_Date::hours */
	public function test_hours_default_and_24h(): void
	{
		// Default (12‑hour) range
		$hours12 = Date::hours();
		$this->assertCount(12, $hours12);
		$this->assertSame('1', $hours12[1]);
		$this->assertSame('12', $hours12[12]);
		// 24‑hour range
		$hours24 = Date::hours(1, true);
		$this->assertCount(24, $hours24);
		$this->assertSame('0', $hours24[0]);
		$this->assertSame('23', $hours24[23]);
	}

	/** @test @covers Kohana_Date::minutes */
	public function test_minutes(): void
	{
		$minutes = Date::minutes();
		$this->assertCount(12, $minutes); // 0‑55 step 5
		$this->assertSame('05', $minutes[5]);
		// The last step should be '55' (no '60' entry)
		$this->assertSame('55', $minutes[55]);
	}

	/** @test @covers Kohana_Date::days */
	public function test_days_february_leap_year(): void
	{
		$days = Date::days(2, 2024); // leap year
		$this->assertCount(29, $days);
		$this->assertSame('29', $days[29]);
	}

	/** @test @covers Kohana_Date::months */
	public function test_months_long_and_short(): void
	{
		$long = Date::months(Date::MONTHS_LONG);
		$this->assertSame('January', $long[1]);
		$short = Date::months(Date::MONTHS_SHORT);
		$this->assertSame('Jan', $short[1]);
	}

	/** @test @covers Kohana_Date::years */
	public function test_years_range(): void
	{
		$years = Date::years(2018, 2020);
		$this->assertCount(3, $years);
		$this->assertSame('2018', $years[2018]);
		$this->assertSame('2020', $years[2020]);
	}
}
