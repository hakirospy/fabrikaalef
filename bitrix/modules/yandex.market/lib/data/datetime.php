<?php

namespace Yandex\Market\Data;

use Bitrix\Main;

class DateTime extends Date
{
	public static function format(Main\Type\Date $date)
	{
		$timestamp = $date->getTimestamp();

		return ConvertTimeStamp($timestamp, 'FULL');
	}

	public static function convertFromService($dateString, $format = 'd-m-Y H:i:s')
	{
		return new Main\Type\DateTime($dateString, $format);
	}

	public static function makeDummy()
	{
		return Main\Type\DateTime::createFromTimestamp(0);
	}
}