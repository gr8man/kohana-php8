<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

	'driver'       => 'File',
	'hash_method'  => 'sha256',
	'hash_key'     => NULL,
	'lifetime'     => 1209600,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user',
	
	// bcrypt cost factor (higher = more secure but slower, 12 is recommended)
	'bcrypt_cost'  => 12,

	// Username/password combinations for the Auth File driver
	'users' => array(
		// 'admin' => '$2y$10$hashedpasswordhere...',
	),

);
