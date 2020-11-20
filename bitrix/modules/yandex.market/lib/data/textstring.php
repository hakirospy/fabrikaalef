<?php

namespace Yandex\Market\Data;

use Bitrix\Main;
use Yandex\Market;

class TextString
{
	public static function getLength($string)
	{
		if (\function_exists('mb_strlen'))
		{
			$result = mb_strlen($string, LANG_CHARSET);
		}
		else
		{
			$result = strlen($string);
		}

		return $result;
	}

	public static function getPosition($haystack, $needle, $offset = 0)
	{
		if (\function_exists('mb_strpos'))
		{
			$result = mb_strpos($haystack, $needle, $offset, LANG_CHARSET);
		}
		else
		{
			$result = strpos($haystack, $needle, $offset);
		}

		return $result;
	}

	public static function getSubstring($string, $from, $length = null)
	{
		if (\function_exists('mb_substr'))
		{
			$result = mb_substr($string, $from, $length, LANG_CHARSET);
		}
		else
		{
			$result = substr($string, $from, $length);
		}

		return $result;
	}

	public static function toUpper($string)
	{
		if (\function_exists('mb_strtoupper'))
		{
			$result = mb_strtoupper($string, LANG_CHARSET);
		}
		else
		{
			$result = strtoupper($string);
		}

		return $result;
	}

	public static function toLower($string)
	{
		if (\function_exists('mb_strtolower'))
		{
			$result = mb_strtolower($string, LANG_CHARSET);
		}
		else
		{
			$result = strtolower($string);
		}

		return $result;
	}
}