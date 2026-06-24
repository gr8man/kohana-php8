<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct access allowed.');

return [

	'driver'       => 'File',
	'hash_method'  => 'sha256',
	'hash_key'     => null,
	'lifetime'     => 1209600,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user',

	// bcrypt cost factor (higher = more secure but slower, 12 is recommended)
	'bcrypt_cost'  => 12,

	// Username/password combinations for the Auth File driver
	'users' => [
		// 'admin' => '$2y$10$hashedpasswordhere...',
	],

];
