<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Woody Gilk <woody.gilk@kohanaphp.com>
 */
class Bench_UserFuncArray extends Codebench
{
	public $description =
		'Testing the speed difference of using <code>call_user_func_array</code>
		 compared to counting args and doing manual calls.';

	public $loops = 100000;

	public $subjects = array(
		// Argument sets
		array(),
		array('one'),
		array('one', 'two'),
		array('one', 'two', 'three'),
	);

	public function bench_count_args($args): void
	{
		$name = 'callme';
		match (count($args)) {
			1 => $this->$name($args[0]),
			2 => $this->$name($args[0], $args[1]),
			3 => $this->$name($args[0], $args[1], $args[2]),
			4 => $this->$name($args[0], $args[1], $args[2], $args[3]),
			default => call_user_func_array(array($this, $name), $args),
		};
	}

	public function bench_direct_call($args): void
	{
		$name = 'callme';
		call_user_func_array(array($this, $name), $args);
	}

	protected function callme(): int
	{
		return count(func_get_args());
	}

}
