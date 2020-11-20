<?php

namespace Yandex\Market\Trading\Setup;

class Facade
{
	public static function hasServiceSetup($serviceCode, $except = null)
	{
		$filter = [
			'=TRADING_SERVICE' => $serviceCode,
		];

		if (!empty($except))
		{
			$filter['!=ID'] = $except;
		}

		$collection = Collection::loadByFilter([
			'filter' => $filter,
			'limit' => 1
		]);

		return (count($collection) > 0);
	}

	public static function hasActiveSetup($siteId, $except = null)
	{
		$filter = [
			'=ACTIVE' => Table::BOOLEAN_Y,
			'=SITE_ID' => $siteId,
		];

		if (!empty($except))
		{
			$filter['!=ID'] = $except;
		}

		$collection = Collection::loadByFilter([
			'filter' => $filter,
			'limit' => 1
		]);

		return (count($collection) > 0);
	}

	public static function hasActiveSetupUsingExternalPlatform($externalId, $except = null)
	{
		$filter = [
			'=ACTIVE' => Table::BOOLEAN_Y,
			'=EXTERNAL_ID' => $externalId,
		];

		if (!empty($except))
		{
			$filter['!=ID'] = $except;
		}

		$collection = Collection::loadByFilter([
			'filter' => $filter,
			'limit' => 1
		]);

		return (count($collection) > 0);
	}
}