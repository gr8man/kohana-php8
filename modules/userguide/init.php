<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');

// Static file serving (CSS, JS, images)
Route::set('docs/media', 'guide-media(/<file>)', array('file' => '.+'))
	->defaults(array(
		'controller' => 'Userguide',
		'action'     => 'media',
		'file'       => null,
	));

// API Browser, if enabled
if (Kohana::$config->load('userguide.api_browser') === true) {
	Route::set('docs/api', 'guide-api(/<class>)', array('class' => '[a-zA-Z0-9_]+'))
		->defaults(array(
			'controller' => 'Userguide',
			'action'     => 'api',
			'class'      => null,
		));
}

// User guide pages, in modules
Route::set('docs/guide', 'guide(/<module>(/<page>))', array(
		'page' => '.+',
	))
	->defaults(array(
		'controller' => 'Userguide',
		'action'     => 'docs',
		'module'     => '',
	));

// Simple autoloader used to encourage PHPUnit to behave itself.
class Markdown_Autoloader
{
	public static function autoload($class)
	{
		if ($class == 'Markdown_Parser' or $class == 'MarkdownExtra_Parser') {
			include_once Kohana::find_file('vendor', 'markdown/markdown');
		}
	}
}

// Register the autoloader
spl_autoload_register(array('Markdown_Autoloader', 'autoload'));
