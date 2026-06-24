<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller
{
	public function action_index(): void
	{
		$this->response->body('hello, world!');
	}

} // End Welcome
