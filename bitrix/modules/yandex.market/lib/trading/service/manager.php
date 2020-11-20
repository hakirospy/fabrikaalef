<?php

namespace Yandex\Market\Trading\Service;

use Yandex\Market;
use Bitrix\Main;

class Manager
{
	const SERVICE_TURBO = 'turbo';
	const SERVICE_MARKETPLACE = 'marketplace';
	const SERVICE_BERU = 'beru';

	protected static $userServices;

	/**
	 * @param string $code
	 *
	 * @return Market\Trading\Service\Reference\Provider
	 * @throws Market\Exceptions\NotImplemented
	 */
	public static function createProvider($code)
	{
		$userServices = static::getUserServices();

		if (isset($userServices[$code]))
		{
			$className = $userServices[$code];
		}
		else if (in_array($code, static::getSystemVariants(), true))
		{
			$className = static::getSystemProviderClassName($code);
		}
		else
		{
			throw new Market\Exceptions\NotImplemented('service provider not implemented for ' . $code);
		}

		return new $className($code);
	}

	public static function isExists($code)
	{
		$services = static::getVariants();

		return in_array($code, $services, true);
	}

	public static function getVariants()
	{
		$systemServices = static::getSystemVariants();
		$userServices = static::getUserServices();

		return array_merge(
			$systemServices,
			array_keys($userServices)
		);
	}

	protected static function getSystemVariants()
	{
		return [
			static::SERVICE_TURBO,
			static::SERVICE_BERU,
			static::SERVICE_MARKETPLACE,
		];
	}

	protected static function getSystemProviderClassName($code)
	{
		return __NAMESPACE__ . '\\' . ucfirst($code) . '\\' . 'Provider';
	}

	protected static function getUserServices()
	{
		if (static::$userServices === null)
		{
			static::$userServices = static::loadUserServices();
		}

		return static::$userServices;
	}

	protected static function loadUserServices()
	{
		$result = [];
		$moduleName = Market\Config::getModuleName();
		$eventName = 'onTradingServiceBuildList';

		$event = new Main\Event($moduleName, $eventName);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() !== Main\EventResult::SUCCESS) { continue; }

			$eventData = $eventResult->getParameters();

			if (!isset($eventData['SERVICE']))
			{
				throw new Main\ArgumentException('SERVICE must be defined for event result ' . $eventName);
			}

			if (!isset($eventData['PROVIDER']))
			{
				throw new Main\ArgumentException('PROVIDER must be defined for service ' . $eventData['SERVICE']);
			}

			if (!is_subclass_of($eventData['PROVIDER'], Reference\Provider::class))
			{
				throw new Main\ArgumentException($eventData['PROVIDER'] . ' must extends ' . Reference\Provider::class . ' for service ' . $eventData['SERVICE']);
			}

			$result[$eventData['SERVICE']] = $eventData['PROVIDER'];
		}

		return $result;
	}
}