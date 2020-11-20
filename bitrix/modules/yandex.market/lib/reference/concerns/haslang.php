<?php

namespace Yandex\Market\Reference\Concerns;

use Yandex\Market;

trait HasLang
{
	protected static $messagesLoaded;

	protected static function loadMessages()
	{
		if (!static::$messagesLoaded)
		{
			static::includeMessages();
			static::$messagesLoaded = true;
		}
	}

	protected static function includeMessages()
	{
		// nothing load in reference
	}

	protected static function getLang($code, $replaces = null, $fallback = null)
	{
		static::loadMessages();

		return Market\Config::getLang($code, $replaces, $fallback);
	}
}