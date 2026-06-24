<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_MDDoBaseURL extends Codebench
{
	public $description =
		'Optimization for the <code>doBaseURL()</code> method of <code>Kohana_Kodoc_Markdown</code>
		 for the Kohana Userguide.';

	public $loops = 10000;

	public $subjects = [
		// Valid matches
		'[filesystem](about.filesystem)',
		'[filesystem](about.filesystem "Optional title")',
		'[same page link](#id)',
		'[object oriented](http://wikipedia.org/wiki/Object-Oriented_Programming)',

		// Invalid matches
		'![this is image syntax](about.filesystem)',
		'[filesystem](about.filesystem',
	];

	public function bench_original($subject): string|array|null
	{
		// The original regex contained a bug, which is fixed here for benchmarking purposes.
		// At the very start of the regex, (?!!) has been replace by (?<!!)
		return preg_replace_callback('~(?<!!)\[(.+?)\]\(([^#]\S*(?:\s*".+?")?)\)~', $this->_add_base_url_original(...), (string) $subject);
	}
	public function _add_base_url_original($matches): string
	{
		if ($matches[2] and !str_contains((string) $matches[2], '://')) {
			// Add the base url to the link URL
			$matches[2] = 'http://BASE/'.$matches[2];
		}

		// Recreate the link
		return "[{$matches[1]}]({$matches[2]})";
	}

	public function bench_optimized_callback($subject): string|array|null
	{
		return preg_replace_callback('~(?<!!)\[(.+?)\]\((?!\w++://)([^#]\S*(?:\s*+".+?")?)\)~', $this->_add_base_url_optimized(...), (string) $subject);
	}
	public function _add_base_url_optimized($matches): string
	{
		// Add the base url to the link URL
		$matches[2] = 'http://BASE/'.$matches[2];

		// Recreate the link
		return "[{$matches[1]}]({$matches[2]})";
	}

	public function bench_callback_gone($subject): string|array|null
	{
		// All the optimized callback was doing now, is prepend some text to the URL.
		// We don't need a callback for that, and that should be clearly faster.
		return preg_replace('~(?<!!)(\[.+?\]\()(?!\w++://)([^#]\S*(?:\s*+".+?")?\))~', '$1http://BASE/$2', (string) $subject);
	}

}
