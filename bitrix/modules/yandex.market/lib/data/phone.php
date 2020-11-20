<?php

namespace Yandex\Market\Data;

use Yandex\Market;

class Phone
{
	const FORMAT_INTERNATIONAL_FORMATTED = 'internationalFormatted';
	const FORMAT_REGIONAL_FORMATTED = 'regionalFormatted';
	const FORMAT_INTERNATIONAL_NUMERIC = 'internationalNumeric';
	const FORMAT_REGIONAL_NUMERIC = 'regionalNumeric';
	const FORMAT_CUSTOM = 'custom';

	protected static $formatMasks = [
		self::FORMAT_INTERNATIONAL_FORMATTED => '+7 495 000-00-00',
		self::FORMAT_REGIONAL_FORMATTED => '8 495 000-00-00',
		self::FORMAT_INTERNATIONAL_NUMERIC => '+74950000000',
		self::FORMAT_REGIONAL_NUMERIC => '84950000000',
	];

	public static function format($value, $format = null)
	{
		if ($format === null) { $format = static::getActiveFormat(); }

		if ($format !== null)
		{
			$mask = static::getMask($format);
			$result = static::applyMask($value, $mask);
		}
		else
		{
			$result = $value;
		}

		return $result;
	}

	public static function getFormatVariants()
	{
		return [
			static::FORMAT_INTERNATIONAL_FORMATTED,
			static::FORMAT_REGIONAL_FORMATTED,
			static::FORMAT_INTERNATIONAL_NUMERIC,
			static::FORMAT_REGIONAL_NUMERIC,
		];
	}

	protected static function getActiveFormat()
	{
		$option = (string)Market\Config::getOption('phone_mask_rule', '');

		return $option !== '' ? $option : null;
	}

	public static function getMask($format)
	{
		if ($format === static::FORMAT_CUSTOM)
		{
			$result = (string)Market\Config::getOption('phone_mask', '');
		}
		else
		{
			$result = static::$formatMasks[$format];
		}

		return $result;
	}

	protected static function applyMask($value, $mask)
	{
		$maskLength = strlen($mask);
		$valueSanitized = static::sanitize($value);
		$valueIndex = 0;
		$valueLength = strlen($valueSanitized);
		$result = '';

		for ($maskIndex = 0; $maskIndex < $maskLength; $maskIndex++)
		{
			if ($valueIndex >= $valueLength) { break; }

			$maskSymbol = substr($mask, $maskIndex, 1);

			if (!is_numeric($maskSymbol))
			{
				$result .= $maskSymbol;
			}
			else if ($valueIndex < $valueLength)
			{
				$valueSymbol = substr($valueSanitized, $valueIndex, 1);

				if ($valueIndex === 0)
				{
					$valueSymbol = static::resolveFirstSymbolCollision($valueSymbol, $maskSymbol, $value, $mask);
				}

				$result .= $valueSymbol;
				++$valueIndex;
			}
		}

		if ($valueIndex < $valueLength)
		{
			$result .= substr($valueSanitized, $valueIndex);
		}

		return $result;
	}

	protected static function sanitize($value)
	{
		return preg_replace('/\D/', '', $value);
	}

	protected static function resolveFirstSymbolCollision($valueSymbol, $maskSymbol, $value, $mask)
	{
		$isValueInternational = static::isInternational($value);
		$isMaskInternational = static::isInternational($mask);
		$result = $valueSymbol;

		if ($isMaskInternational !== $isValueInternational)
		{
			if ($isMaskInternational && $maskSymbol === '7' && $valueSymbol === '8')
			{
				$result = $maskSymbol;
			}
			else if (!$isMaskInternational && $maskSymbol === '8' && $valueSymbol === '7')
			{
				$result = $maskSymbol;
			}
		}

		return $result;
	}

	protected static function isInternational($value)
	{
		return strpos($value, '+') === 0;
	}
}