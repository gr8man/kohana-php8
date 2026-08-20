<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');

return array(

	'default' => array(
		/**
		 * The following options must be set:
		 *
		 * string   key     secret passphrase
		 * string   method  openssl cipher method (e.g. aes-256-cbc)
		 */
		'key'    => 'default_secret_key_change_me_in_production!',
		'method' => 'aes-256-cbc',
	),

);
