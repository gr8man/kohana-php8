<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * STDOUT log writer. Writes out messages to STDOUT.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_StdOut extends Log_Writer
{
	/**
	 * @var resource Stream handle to write to (defaults to STDOUT)
	 */
	protected mixed $_handle;

	/**
	 * Create STDOUT writer with optional custom stream (useful for testing).
	 *
	 * @param resource|null $handle Stream resource, defaults to STDOUT
	 */
	public function __construct(mixed $handle = null)
	{
		if ($handle === null) {
			$this->_handle = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
		} else {
			$this->_handle = $handle;
		}
	}

	/**
	 * Writes each of the messages to STDOUT.
	 *
	 *     $writer->write($messages);
	 */
	#[\Override]
	public function write(array $messages): void
	{
		foreach ($messages as $message) {
			// Writes out each message
			fwrite($this->_handle, $this->format_message($message).PHP_EOL);
		}
	}

}
