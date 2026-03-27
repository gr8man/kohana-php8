<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Syslog log writer.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Jeremy Bush
 * @copyright  (c) 2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Syslog extends Log_Writer {

	protected string $_ident;

	/**
	 * Creates a new syslog logger.
	 *
	 * @link    http://www.php.net/manual/function.openlog
	 *
	 * @param   string  $ident      syslog identifier
	 * @param   int     $facility   facility to log to
	 */
	public function __construct(string $ident = 'KohanaPHP', int $facility = LOG_USER)
	{
		$this->_ident = $ident;

		openlog($this->_ident, LOG_CONS, $facility);
	}

	/**
	 * Writes each of the messages into the syslog.
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			syslog($message['level'], $message['body']);

			if (isset($message['additional']['exception']))
			{
				syslog(Log_Writer::$strace_level, $message['additional']['exception']->getTraceAsString());
			}
		}
	}

	/**
	 * Closes the syslog connection
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		// Close connection to syslog
		closelog();
	}

}
