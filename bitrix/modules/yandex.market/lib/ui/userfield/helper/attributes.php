<?php

namespace Yandex\Market\Ui\UserField\Helper;

class Attributes
{
	public static function convertNameToId($name)
	{
		$result = str_replace(['[', ']', '-', '__'], '_', $name);
		$result = trim($result, '_');

		return $result;
	}

	public static function extractFromSettings($userFieldSettings, $settingNames = null)
	{
		$result = isset($userFieldSettings['ATTRIBUTES']) ? (array)$userFieldSettings['ATTRIBUTES'] : [];

		if ($settingNames === null)
		{
			$settingNames = [
				'READONLY',
				'STYLE',
				'PLACEHOLDER',
			];
		}

		foreach ($settingNames as $settingName)
		{
			if (
				isset($userFieldSettings[$settingName])
				&& $userFieldSettings[$settingName] !== ''
				&& $userFieldSettings[$settingName] !== false
			)
			{
				$setting = $userFieldSettings[$settingName];
				$attributeName = strtolower($settingName);

				$result[$attributeName] = $setting;
			}
		}

		return $result;
	}

	public static function insertInput($html, $attributes)
	{
		if (!empty($attributes))
		{
			$attributesString = static::stringify($attributes);
			$result = preg_replace('/(<input|<textarea|<select)(.*?)(\/?>)/si', '$1$2 ' . $attributesString . '$3', $html);
		}
		else
		{
			$result = $html;
		}

		return $result;
	}

	public static function stringify($attributes)
	{
		if (is_array($attributes))
		{
			$htmlAttributes = [];

			foreach ($attributes as $key => $value)
			{
				if (is_numeric($key))
				{
					$htmlAttributes[] = $value;
				}
				else if ($value === false || $value === null)
				{
					// skip
				}
				else if ($value === true || (string)$value === '')
				{
					$htmlAttributes[] = htmlspecialcharsbx($key);
				}
				else
				{
					$htmlAttributes[] = htmlspecialcharsbx($key) . '="' . htmlspecialcharsbx($value) . '"';
				}
			}

			$result = implode(' ', $htmlAttributes);
		}
		else
		{
			$result = (string)$attributes;
		}

		return $result;
	}
}