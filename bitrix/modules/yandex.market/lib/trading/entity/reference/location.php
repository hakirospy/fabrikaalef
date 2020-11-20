<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

abstract class Location
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @param array{id: int, name: string, parent: array|null} $serviceRegion
	 *
	 * @return int|null
	 */
	public function getLocation($serviceRegion)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getLocation');
	}
}