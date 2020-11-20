<?php

namespace Yandex\Market\Data;

use Bitrix\Main;

class Date
{
	const FORMAT_DEFAULT_FULL = 'd-m-Y H:i:s';
	const FORMAT_DEFAULT_SHORT = 'd-m-Y';

	public static function format(Main\Type\Date $date)
	{
		$timestamp = $date->getTimestamp();

		return ConvertTimeStamp($timestamp, 'SHORT');
	}

	public static function convertFromService($dateString, $format = 'd-m-Y')
	{
		return new Main\Type\Date($dateString, $format);
	}

	public static function convertForService($timestamp, $format = \DateTime::ATOM)
	{
		if ($timestamp instanceof Main\Type\Date)
		{
			$timestamp = $timestamp->getTimestamp();
		}

		return date($format, $timestamp);
	}

	public static function isValid($date)
	{
		return $date instanceof Main\Type\Date && !static::isDummy($date);
	}

	public static function isDummy($date)
	{
		return $date instanceof Main\Type\Date && $date->getTimestamp() === 0;
	}

	public static function makeDummy()
	{
		return Main\Type\Date::createFromTimestamp(0);
	}
}