<?php

/**
 * Custom Kohana Class Integrity Checker for PHP 8.3 Migration
 */

$application = 'application';
$modules = 'modules';
$system = 'system';
define('EXT', '.php');
error_reporting(E_ALL | E_STRICT);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('APPPATH', realpath(DOCROOT.$application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath(DOCROOT.$modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath(DOCROOT.$system).DIRECTORY_SEPARATOR);

unset($application, $modules, $system);

require APPPATH.'bootstrap'.EXT;

echo "Starting class checks...\n";

$classes = Kohana::list_files('classes');
$flattened = Arr::flatten($classes);

$count = 0;
$errors = 0;

foreach ($flattened as $path)
{
	$className = NULL;
	foreach (Kohana::include_paths() as $includePath)
	{
		if (strpos($path, $includePath) === 0)
		{
			$rel = substr($path, strlen($includePath));
			if (strpos($rel, 'classes'.DIRECTORY_SEPARATOR) === 0)
			{
				$rel = substr($rel, 8); // remove classes/
				$rel = substr($rel, 0, -4); // remove .php
				$className = str_replace(DIRECTORY_SEPARATOR, '_', $rel);
				break;
			}
		}
	}
	
	if ($className)
	{
		if (strpos($className, 'Unittest') !== FALSE OR strpos($className, 'Kohana_Unittest') !== FALSE)
		{
			continue;
		}

		$count++;
		try
		{
			if (class_exists($className) OR interface_exists($className))
			{
				$ref = new ReflectionClass($className);
			}
			else
			{
				echo "FAIL: $className (not found)\n";
				$errors++;
			}
		}
		catch (Throwable $e)
		{
			echo "FAIL: $className\n";
			echo "  " . $e->getMessage() . "\n";
			$errors++;
		}
	}
}

echo "\nChecked $count classes. Errors: $errors\n";
exit($errors > 0 ? 1 : 0);
