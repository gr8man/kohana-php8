<?php

declare(strict_types=1);
defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie-based session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Session_Cookie extends Session
{
	/**
	 * @param   string  $id  session id
	 * @return  string
	 */
	#[\Override]
	protected function _read($id = null)
	{
		return Cookie::get($this->_name);
	}

	#[\Override]
	protected function _regenerate(): null
	{
		// Cookie sessions have no id
		return null;
	}

	#[\Override]
	protected function _write(): bool
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	#[\Override]
	protected function _restart(): bool
	{
		return true;
	}

	#[\Override]
	protected function _destroy(): bool
	{
		return Cookie::delete($this->_name);
	}

}
