<?php

declare(strict_types=1); defined('SYSPATH') OR die('No direct script access.');

return array(

	'trusted_hosts' => array(
		// SECURITY: Configure trusted hosts to prevent HTTP Host header attacks
		//
		// This prevents attackers from injecting malicious host headers
		// which could be used for cache poisoning or XSS attacks
		//
		// Examples:
		//
		//        'example\.org',
		//        '.*\.example\.org',
		//        'localhost',
		//
		// Do not forget to escape your dots (.) as these are regex patterns.
		// These patterns should always fully match,
		// as they are prepended with `^` and appended with `$`
	),

);
