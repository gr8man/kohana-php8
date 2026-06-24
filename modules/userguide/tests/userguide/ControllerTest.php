<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for internal methods of userguide controller
 *
 * @group kohana
 * @group kohana.userguide
 * @group kohana.userguide.controller
 *
 * @package    Kohana/Userguide
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Userguide_ControllerTest extends Unittest_TestCase
{
	public function provider_file_finds_markdown_files(): array
	{
		return [
			['userguide/adding', 'guide/userguide/adding.md'],
			['userguide/adding.md', 'guide/userguide/adding.md'],
			['userguide/adding.markdown', 'guide/userguide/adding.md'],
			['userguide/does_not_exist.md', false]
		];
	}

	/**
	 * @dataProvider provider_file_finds_markdown_files
	 * @param  string  $page           Page name passed in the URL
	 * @param  string  $expected_file  Expected result from Controller_Userguide::file
	 */
	public function test_file_finds_markdown_files(string $page, string|bool $expected_file): void
	{
		$controller = $this->getMock('Controller_Userguide', ['__construct'], [], '', false);
		$path = $controller->file($page);

		// Only verify trailing segments to avoid problems if file overwritten in CFS
		$expected_len = strlen($expected_file);
		$file = substr((string) $path, -$expected_len, $expected_len);

		$this->assertEquals($expected_file, $file);
	}

}
