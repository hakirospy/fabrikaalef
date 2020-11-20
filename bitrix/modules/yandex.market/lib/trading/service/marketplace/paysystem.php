<?php

namespace Yandex\Market\Trading\Service\Marketplace;

use Yandex\Market;
use Bitrix\Main;

class PaySystem
{
	use Market\Reference\Concerns\HasLang;

	protected $provider;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

	public function getTypeTitle($type, $version = '')
	{
		$typeKey = strtoupper($type);
		$versionSuffix = ($version !== '' ? '_' . $version : '');

		return static::getLang('TRADING_SERVICE_MARKETPLACE_PAY_SYSTEM_TYPE_' . $typeKey . $versionSuffix, null, $type);
	}

	public static function getMethodTitle($method, $version = '')
	{
		$methodKey = strtoupper($method);
		$versionSuffix = ($version !== '' ? '_' . $version : '');

		return static::getLang('TRADING_SERVICE_MARKETPLACE_PAY_SYSTEM_METHOD_' . $methodKey . $versionSuffix, null, $method);
	}
}