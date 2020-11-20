<?php

namespace Yandex\Market\Trading\Setup;

use Bitrix\Main;
use Yandex\Market;

class Collection extends Market\Reference\Storage\Collection
{
	public static function getItemReference()
	{
		return Model::getClassName();
	}

	public static function loadByService($serviceCode)
	{
		return static::loadByFilter([
			'filter' => [ '=TRADING_SERVICE' => $serviceCode ]
		]);
	}

	public function getBySite($siteId)
	{
		$result = null;

		/** @var Model $setup*/
		foreach ($this->collection as $setup)
		{
			if ($setup->getSiteId() === $siteId)
			{
				$result = $setup;
				break;
			}
		}

		return $result;
	}

	public function getActive()
	{
		$result = null;

		/** @var Model $setup*/
		foreach ($this->collection as $setup)
		{
			if ($setup->isActive())
			{
				$result = $setup;
				break;
			}
		}

		return $result;
	}
}