<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

class PaySystem
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return bool
	 */
	public function isRequired()
	{
		return false;
	}

	/**
	 * @param string|null $siteId
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getEnum($siteId = null)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getEnum');
	}
}