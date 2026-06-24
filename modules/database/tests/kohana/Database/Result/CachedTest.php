<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Database_Result_Cached - the array-based database result class.
 *
 * @group kohana
 * @group kohana.database
 * @group kohana.database.result
 *
 * @package    Kohana/Database
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2024 Kohana Team
 * @license    http://kohanaphp.com/license
 */
#[AllowDynamicProperties]
class Kohana_Database_Result_CachedTest extends Unittest_TestCase
{
	/**
	 * Provides sample result data.
	 */
	public function provider_result_data(): array
	{
		return [
			[
				[
					['id' => 1, 'name' => 'John'],
					['id' => 2, 'name' => 'Jane'],
					['id' => 3, 'name' => 'Bob'],
				],
			],
			[
				[
					['id' => 10, 'title' => 'First', 'active' => true],
					['id' => 20, 'title' => 'Second', 'active' => false],
				],
			],
			[
				[],
			],
		];
	}

	/**
	 * Provides test data for get().
	 */
	public function provider_get(): array
	{
		return [
			// existing column
			[
				[['id' => 1, 'name' => 'John']],
				'name',
				null,
				'John',
			],
			// non-existing column with default
			[
				[['id' => 1, 'name' => 'John']],
				'age',
				0,
				0,
			],
			// non-existing column without default
			[
				[['id' => 1, 'name' => 'John']],
				'age',
				null,
				null,
			],
		];
	}

	/**
	 * @test
	 * @covers Kohana_Database_Result_Cached::__construct
	 * @covers Database_Result::count
	 * @covers Database_Result::count
	 */
	public function test_count(): void
	{
		$data = [
			['id' => 1],
			['id' => 2],
			['id' => 3],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$this->assertCount(3, $result);
		$this->assertSame(3, $result->count());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::count
	 */
	public function test_count_empty(): void
	{
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$this->assertCount(0, $result);
		$this->assertSame(0, $result->count());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::current
	 * @covers Database_Result::key
	 */
	public function test_current_and_key(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 42, 'name' => 'Answer'],
			],
			'SELECT * FROM test'
		);
		$this->assertSame(['id' => 42, 'name' => 'Answer'], $result->current());
		$this->assertSame(0, $result->key());
	}

	/**
	 * @test
	 * @covers Database_Result::next
	 * @covers Database_Result::current
	 */
	public function test_next(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
				['id' => 2],
			],
			'SELECT * FROM test'
		);
		$result->next();
		$this->assertSame(['id' => 2], $result->current());
		$this->assertSame(1, $result->key());
	}

	/**
	 * @test
	 * @covers Database_Result::prev
	 * @covers Database_Result::current
	 */
	public function test_prev(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
				['id' => 2],
			],
			'SELECT * FROM test'
		);
		$result->next();
		$result->prev();
		$this->assertSame(['id' => 1], $result->current());
		$this->assertSame(0, $result->key());
	}

	/**
	 * @test
	 * @covers Database_Result::rewind
	 * @covers Database_Result::current
	 */
	public function test_rewind(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
				['id' => 2],
			],
			'SELECT * FROM test'
		);
		$result->next();
		$result->rewind();
		$this->assertSame(['id' => 1], $result->current());
		$this->assertSame(0, $result->key());
	}

	/**
	 * @test
	 * @covers Database_Result::valid
	 */
	public function test_valid(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
			],
			'SELECT * FROM test'
		);
		$this->assertTrue($result->valid());
	}

	/**
	 * @test
	 * @covers Database_Result::valid
	 */
	public function test_valid_false_when_past_end(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
			],
			'SELECT * FROM test'
		);
		$result->next();
		$this->assertFalse($result->valid());
	}

	/**
	 * @test
	 * @covers Database_Result::valid
	 */
	public function test_valid_empty(): void
	{
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$this->assertFalse($result->valid());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::seek
	 */
	public function test_seek(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
				['id' => 2],
				['id' => 3],
			],
			'SELECT * FROM test'
		);
		$result->seek(2);
		$this->assertSame(['id' => 3], $result->current());
		$this->assertSame(2, $result->key());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::seek
	 * @covers Database_Result::current
	 */
	public function test_seek_invalid_offset(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
			],
			'SELECT * FROM test'
		);
		// seek to invalid offset should silently do nothing
		$result->seek(999);
		$this->assertSame(['id' => 1], $result->current());
	}

	/**
	 * @test
	 * @covers Database_Result::offsetExists
	 */
	public function test_offset_exists(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
				['id' => 2],
			],
			'SELECT * FROM test'
		);
		$this->assertTrue(isset($result[0]));
		$this->assertTrue(isset($result[1]));
		$this->assertFalse(isset($result[2]));
		$this->assertFalse(isset($result[-1]));
	}

	/**
	 * @test
	 * @covers Database_Result::offsetGet
	 */
	public function test_offset_get(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1, 'name' => 'John'],
				['id' => 2, 'name' => 'Jane'],
			],
			'SELECT * FROM test'
		);
		$this->assertSame(['id' => 1, 'name' => 'John'], $result[0]);
		$this->assertSame(['id' => 2, 'name' => 'Jane'], $result[1]);
	}

	/**
	 * @test
	 * @covers Database_Result::offsetGet
	 */
	public function test_offset_get_invalid(): void
	{
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$this->assertNull($result[999]);
	}

	/**
	 * @test
	 * @covers Database_Result::offsetSet
	 */
	public function test_offset_set_throws(): void
	{
		$this->expectException(Kohana_Exception::class);
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$result[0] = 'test';
	}

	/**
	 * @test
	 * @covers Database_Result::offsetUnset
	 */
	public function test_offset_unset_throws(): void
	{
		$this->expectException(Kohana_Exception::class);
		$result = new Database_Result_Cached(
			[['id' => 1]],
			'SELECT * FROM test'
		);
		unset($result[0]);
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::cached
	 */
	public function test_cached_returns_self(): void
	{
		$result = new Database_Result_Cached(
			[['id' => 1]],
			'SELECT * FROM test'
		);
		$this->assertSame($result, $result->cached());
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_as_array_indexed(): void
	{
		$data = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$this->assertSame($data, $result->as_array());
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_as_array_keyed(): void
	{
		$data = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];
		$expected = [
			1 => ['id' => 1, 'name' => 'John'],
			2 => ['id' => 2, 'name' => 'Jane'],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$this->assertSame($expected, $result->as_array('id'));
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_as_array_key_value(): void
	{
		$data = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];
		$expected = [
			1 => 'John',
			2 => 'Jane',
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$this->assertSame($expected, $result->as_array('id', 'name'));
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_as_array_empty(): void
	{
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$this->assertSame([], $result->as_array());
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_as_array_value_only(): void
	{
		$data = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];
		$expected = ['John', 'Jane'];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$this->assertSame($expected, $result->as_array(null, 'name'));
	}

	/**
	 * @test
	 * @covers Database_Result::get
	 */
	public function test_get(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1, 'name' => 'John'],
			],
			'SELECT * FROM test'
		);
		$this->assertSame('John', $result->get('name'));
	}

	/**
	 * @test
	 * @covers Database_Result::get
	 */
	public function test_get_default(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1, 'name' => 'John'],
			],
			'SELECT * FROM test'
		);
		$this->assertSame(0, $result->get('age', 0));
	}

	/**
	 * @test
	 * @covers Database_Result::get
	 */
	public function test_get_null_default(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1, 'name' => 'John'],
			],
			'SELECT * FROM test'
		);
		$this->assertNull($result->get('age'));
	}

	/**
	 * @test
	 * @covers Database_Result::get
	 */
	public function test_get_non_existent_default_null(): void
	{
		$result = new Database_Result_Cached(
			[
				['id' => 1],
			],
			'SELECT * FROM test'
		);
		$this->assertNull($result->get('name'));
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 * Ensures foreach iteration works correctly
	 */
	public function test_foreach_iteration(): void
	{
		$data = [
			['id' => 1],
			['id' => 2],
			['id' => 3],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$iterated = [];
		foreach ($result as $key => $row) {
			$iterated[$key] = $row;
		}
		$this->assertSame($data, $iterated);
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 * Ensures rewind works for multiple foreach loops
	 */
	public function test_multiple_foreach(): void
	{
		$data = [
			['id' => 1],
			['id' => 2],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		$first = [];
		foreach ($result as $row) {
			$first[] = $row;
		}
		$second = [];
		foreach ($result as $row) {
			$second[] = $row;
		}
		$this->assertSame($first, $second);
	}

	/**
	 * @test
	 * @covers Database_Result::count
	 * count() after iteration should still return total
	 */
	public function test_count_after_iteration(): void
	{
		$data = [
			['id' => 1],
			['id' => 2],
			['id' => 3],
		];
		$result = new Database_Result_Cached($data, 'SELECT * FROM test');
		foreach ($result as $row) {
			// just iterate
		}
		$this->assertCount(3, $result);
		$this->assertSame(3, $result->count());
	}

	/**
	 * @test
	 * Interface compliance test
	 */
	public function test_implements_interfaces(): void
	{
		$result = new Database_Result_Cached([], 'SELECT * FROM test');
		$this->assertInstanceOf(Countable::class, $result);
		$this->assertInstanceOf(Iterator::class, $result);
		$this->assertInstanceOf(SeekableIterator::class, $result);
		$this->assertInstanceOf(ArrayAccess::class, $result);
	}
}
