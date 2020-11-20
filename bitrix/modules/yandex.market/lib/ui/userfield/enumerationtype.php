<?php

namespace Yandex\Market\Ui\UserField;

use Yandex\Market;
use Bitrix\Main;

class EnumerationType extends \CUserTypeEnum
{
	function GetList($arUserField)
	{
		$values = (array)$arUserField['VALUES'];

		$result = new \CDBResult();
		$result->InitFromArray($values);

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$attributes = Helper\Attributes::extractFromSettings($arUserField['SETTINGS']);

		if (isset($arUserField['SETTINGS']['DISPLAY']) && $arUserField['SETTINGS']['DISPLAY'] === 'CHECKBOX')
		{
			$arUserField['MANDATORY'] = 'Y'; // hide no value for all variants

			$result = parent::GetEditFormHTML($arUserField, $arHtmlControl);
			$result = Helper\Attributes::insertInput($result, $attributes);
		}
		else
		{
			// settings

			$settings = (array)$arUserField['SETTINGS'];

			if (!isset($settings['ALLOW_NO_VALUE']) && $arUserField['MANDATORY'] !== 'Y')
			{
				$settings['ALLOW_NO_VALUE'] = 'Y';
			}

			// value

			if ($arUserField['ENTITY_VALUE_ID'] < 1 && (string)$arUserField['SETTINGS']['DEFAULT_VALUE'] !== '')
			{
				$arHtmlControl['VALUE'] = $arUserField['SETTINGS']['DEFAULT_VALUE'];
			}
			else if ($arHtmlControl['VALUE'] === '' && array_key_exists('VALUE', $arUserField) && $arUserField['VALUE'] === null)
			{
				$arHtmlControl['VALUE'] =
					isset($arUserField['SETTINGS']['DEFAULT_VALUE'])
						? $arUserField['SETTINGS']['DEFAULT_VALUE']
						: null;
			}

			// attributes

			$attributes['name'] = $arHtmlControl['NAME'];

			if ($arUserField['EDIT_IN_LIST'] !== 'Y')
			{
				$attributes['disabled'] = true;
			}

			if ($arUserField['SETTINGS']['LIST_HEIGHT'] > 1)
			{
				$attributes['size'] = $arUserField['SETTINGS']['LIST_HEIGHT'];
			}
			else
			{
				$arHtmlControl['VALIGN'] = 'middle';
			}

			// view

			$queryEnum = call_user_func(
				[ $arUserField['USER_TYPE']['CLASS_NAME'], 'getList'],
				$arUserField
			);

			$result = View\Select::getControl($queryEnum, $arHtmlControl['VALUE'], $attributes, $settings);
		}

		return $result;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		$result = '&nbsp;';
		$isFoundResult = false;

		if (!empty($arHtmlControl['VALUE']))
		{
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getlist' ], $arUserField);

			if ($query)
			{
				while ($option = $query->Fetch())
				{
					if ($option['ID'] == $arHtmlControl['VALUE'])
					{
						$isFoundResult = true;
						$result = Market\Utils::htmlEscape($option['VALUE']);
						break;
					}
				}
			}

			if (!$isFoundResult)
			{
				$result = '[' . Market\Utils::htmlEscape($arHtmlControl['VALUE']) . ']';
			}
		}

		return $result;
	}

	function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		$result = '';

		if (!empty($arHtmlControl['VALUE']))
		{
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getlist' ], $arUserField);
			$valueList = (array)$arHtmlControl['VALUE'];
			$valueMap = array_flip($valueList);

			if ($query)
			{
				while ($option = $query->Fetch())
				{
					if (isset($valueMap[$option['ID']]))
					{
						$result .= ($result !== '' ? ' / ' : '') . Market\Utils::htmlEscape($option['VALUE']);
					}
				}
			}
		}

		if ($result === '')
		{
			$result = '&nbsp;';
		}

		return $result;
	}
}