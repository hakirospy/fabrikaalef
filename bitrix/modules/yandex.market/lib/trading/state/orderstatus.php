<?php

namespace Yandex\Market\Trading\State;

use Yandex\Market;
use Bitrix\Main;

class OrderStatus
{
	protected static $values = [];

	public static function isChanged($serviceUniqueKey, $orderId, $status)
	{
		return (static::getValue($serviceUniqueKey, $orderId) !== $status);
	}

	public static function setValue($serviceUniqueKey, $orderId, $status)
	{
		$key = $serviceUniqueKey . ':' . $orderId;

		static::$values[$key] = $status;
	}

	public static function getValue($serviceUniqueKey, $orderId)
	{
		$key = $serviceUniqueKey . ':' . $orderId;

		return isset(static::$values[$key]) ? static::$values[$key] : null;
	}

	public static function releaseValue($serviceUniqueKey, $orderId)
	{
		$key = $serviceUniqueKey . ':' . $orderId;

		if (isset(static::$values[$key]))
		{
			unset(static::$values[$key]);
		}
	}
}