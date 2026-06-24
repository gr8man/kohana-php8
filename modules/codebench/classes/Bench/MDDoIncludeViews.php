<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_MDDoIncludeViews extends Codebench
{
	public $description =
		'Optimization for the <code>doIncludeViews()</code> method of <code>Kohana_Kodoc_Markdown</code>
		 for the Kohana Userguide.';

	public $loops = 10000;

	public $subjects = array(
		// Valid matches
		'{{one}} two {{three}}',
		'{{userguide/examples/hello_world_error}}',

		// Invalid matches
		'{}',
		'{{}}',
		'{{userguide/examples/hello_world_error}',
		'{{userguide/examples/hello_world_error }}',
		'{{userguide/examples/{{hello_world_error }}',
	);

	/**
	 * @return string[][]
	 *
	 * @psalm-return list<array<array-key, string>>
	 */
	public function bench_original($subject): array
	{
		preg_match_all('/{{(\S+?)}}/m', (string) $subject, $matches, PREG_SET_ORDER);
		return $matches;
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return list<array<array-key, string>>
	 */
	public function bench_possessive($subject): array
	{
		// Using a possessive character class
		// Removed useless /m modifier
		preg_match_all('/{{([^\s{}]++)}}/', (string) $subject, $matches, PREG_SET_ORDER);
		return $matches;
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return list<array<array-key, string>>
	 */
	public function bench_lookaround($subject): array
	{
		// Using lookaround to move $mathes[1] into $matches[0]
		preg_match_all('/(?<={{)[^\s{}]++(?=}})/', (string) $subject, $matches, PREG_SET_ORDER);
		return $matches;
	}

}
