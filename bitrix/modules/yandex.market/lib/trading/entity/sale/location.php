<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Sale;

class Location extends Market\Trading\Entity\Reference\Location
{
	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getLocation($serviceRegion)
	{
		$result = null;
		$mapper = new \CSaleYMLocation;

		if (method_exists($mapper, 'getLocationId'))
		{
			$locationId = $mapper->getLocationId($serviceRegion);
		}
		else
		{
			$locationId = $mapper->getLocationByCityName($serviceRegion['name']);
		}

		if ($locationId !== false)
		{
			$result = $locationId;
		}

		return $result;
	}
}