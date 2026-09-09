<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');

/**
 * A version of the stock PHPUnit testsuite that supports whitelisting and
 * blacklisting for code coverage filter
 */
abstract class Kohana_Unittest_TestSuite extends PHPUnit\Framework\TestSuite
{
	/**
	 * Holds the details of files that should be white and blacklisted for
	 * code coverage
	 *
	 * @var array
	 */
	protected $_filter_calls = array(
		'addFileToBlacklist' => array(),
		'addDirectoryToBlacklist' => array(),
		'addFileToWhitelist' => array());

	/**
	 * Runs the tests and collects their result in a TestResult.
	 */
	#[\Override]
	public function run(): void
	{
		parent::run();
	}

	/**
	 * Queues a file to be added to the code coverage blacklist when the suite runs
	 * @param string $file
	 */
	public function addFileToBlacklist($file): void
	{
		$this->_filter_calls['addFileToBlacklist'][] = $file;
	}

	/**
	 * Queues a directory to be added to the code coverage blacklist when the suite runs
	 * @param string $dir
	 */
	public function addDirectoryToBlacklist($dir): void
	{
		$this->_filter_calls['addDirectoryToBlacklist'][] = $dir;
	}

	/**
	 * Queues a file to be added to the code coverage whitelist when the suite runs
	 * @param string $file
	 */
	public function addFileToWhitelist($file): void
	{
		$this->_filter_calls['addFileToWhitelist'][] = $file;
	}
}
