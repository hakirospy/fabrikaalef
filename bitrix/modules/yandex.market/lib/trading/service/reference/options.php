<?php

namespace Yandex\Market\Trading\Service\Reference;

use Bitrix\Main;
use Yandex\Market;
use Yandex\Market\Trading\Entity as TradingEntity;

abstract class Options
{
	protected $provider;
	protected $values;

	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

	abstract public function getTitle();

	abstract public function getTabs();

	abstract public function getFields(TradingEntity\Reference\Environment $environment, $siteId);

	public function getSetupId()
	{
		return $this->getRequiredValue('SETUP_ID');
	}

	public function getSiteId()
	{
		return $this->getRequiredValue('SITE_ID');
	}

	public function getPlatformId()
	{
		return $this->getRequiredValue('PLATFORM_ID');
	}

	public function setValues(array $values)
	{
		$this->values = $values;
	}

	public function getValue($key)
	{
		return isset($this->values[$key]) ? $this->values[$key] : null;
	}

	public function getRequiredValue($key)
	{
		$result = $this->getValue($key);

		if (Market\Utils\Value::isEmpty($result))
		{
			throw new Main\SystemException('Required option ' . $key . ' not set');
		}

		return $result;
	}

	public function getValues()
	{
		return $this->values;
	}
}