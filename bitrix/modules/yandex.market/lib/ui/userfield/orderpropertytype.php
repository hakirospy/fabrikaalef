<?php

namespace Yandex\Market\Ui\UserField;

use Yandex\Market;
use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

class OrderPropertyType extends EnumerationType
{
	protected static $variants = [];

	function GetList($arUserField)
	{
		$personType = static::extractUserFieldPersonType($arUserField);
		$variants = static::getVariants($personType);
		$variants = static::markDefaultVariantByType($arUserField, $variants);

		$result = new \CDBResult();
		$result->InitFromArray($variants);

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$htmlId = Market\Ui\UserField\Helper\Attributes::convertNameToId($arHtmlControl['NAME']) . '_REFRESH';

		if (!isset($arUserField['SETTINGS']['ATTRIBUTES']))
		{
			$arUserField['SETTINGS']['ATTRIBUTES'] = [];
		}

		$arUserField['SETTINGS']['ATTRIBUTES']['id'] = $htmlId;

		$result = parent::GetEditFormHTML($arUserField, $arHtmlControl);
		$result .= static::getRefreshScript($arUserField, $htmlId);

		static::loadLangMessages();

		return $result;
	}

	protected static function markDefaultVariantByType($userField, $variants)
	{
		if (isset($userField['SETTINGS']['TYPE']))
		{
			$type = $userField['SETTINGS']['TYPE'];

			foreach ($variants as &$variant)
			{
				if (isset($variant['TYPE']) && $variant['TYPE'] === $type)
				{
					$variant['DEF'] = 'Y';
					break;
				}
			}
			unset($variant);
		}

		return $variants;
	}

	protected static function getRefreshScript($userField, $htmlId)
	{
		$personTypeValue = static::extractUserFieldPersonType($userField);
		$personTypeField = static::getUserFieldPersonTypeField($userField);

		Market\Ui\Assets::loadPlugin('Ui.Input.OrderPropertyRefresh');

		return Market\Ui\Assets::initPlugin('Ui.Input.OrderPropertyRefresh', '#' . $htmlId, [
			'type' => isset($userField['SETTINGS']['TYPE']) ? $userField['SETTINGS']['TYPE'] : null,
			'refreshUrl' => static::getRefreshUrl(),
			'personTypeId' => $personTypeValue,
			'personTypeElement' => 'select[name="' . $personTypeField . '"]',
		]);
	}

	protected static function loadLangMessages()
	{
		Market\Ui\Assets::loadMessages([
			'USER_FIELD_ORDER_PROPERTY_REFRESH_FAIL',
		]);
	}

	protected static function getRefreshUrl()
	{
		return BX_ROOT . '/tools/' . Market\Config::getModuleName() . '/orderproperty/enum.php';
	}

	protected static function extractUserFieldPersonType($userField)
	{
		$fieldPersonType = static::getUserFieldPersonTypeField($userField);
		$result = null;

		if ($fieldPersonType !== null && isset($userField['ROW'][$fieldPersonType]))
		{
			$result = $userField['ROW'][$fieldPersonType];
		}
		else if (isset($userField['SETTINGS']['PERSON_TYPE_DEFAULT']))
		{
			$result = $userField['SETTINGS']['PERSON_TYPE_DEFAULT'];
		}

		return $result;
	}

	protected static function getUserFieldPersonTypeField($userField)
	{
		$result = null;

		if (isset($userField['SETTINGS']['PERSON_TYPE_FIELD']))
		{
			$result = $userField['SETTINGS']['PERSON_TYPE_FIELD'];
		}

		return $result;
	}

	public static function getVariants($personType)
	{
		if (!isset(static::$variants[$personType]))
		{
			static::$variants[$personType] = static::loadVariants($personType);
		}

		return static::$variants[$personType];
	}

	protected static function loadVariants($personType)
	{
		$environment = Market\Trading\Entity\Manager::createEnvironment();

		try
		{
			$property = $environment->getProperty();
			$result = $property->getEnum($personType);
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}
}