<?php

namespace Yandex\Market\Export\Xml\Tag;

use Bitrix\Main;
use Yandex\Market;

class CategoryId extends Base
{
	use Market\Reference\Concerns\HasLang;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function getDefaultParameters()
	{
		return [
			'name' => 'categoryId',
			'value_type' => Market\Type\Manager::TYPE_CATEGORY
		];
	}

	public function getSourceRecommendation(array $context = [])
	{
		$result = [
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_IBLOCK_ELEMENT_FIELD,
				'FIELD' => 'IBLOCK_SECTION_ID'
			]
		];

		if (isset($context['OFFER_IBLOCK_ID']))
		{
			$result[] = [
				'TYPE' => Market\Export\Entity\Manager::TYPE_IBLOCK_OFFER_FIELD,
				'FIELD' => 'IBLOCK_SECTION_ID'
			];
		}

		return $result;
	}

	public function getSettingsDescription(array $context = [])
	{
		$result = [];

		if (Market\Config::isExpertMode())
		{
			$langKey = $this->getLangKey();

			$result['NAME_FIELD'] = [
				'TITLE' => static::getLang($langKey . '_SETTINGS_NAME_FIELD_TITLE'),
				'TYPE' => 'enumeration',
				'VALUES' => $this->getSectionFieldEnum($context),
			];

			$result['USE_BREADCRUMBS'] = [
				'TITLE' => static::getLang($langKey . '_SETTINGS_NAME_FIELD_USE_BREADCRUMBS'),
				'DESCRIPTION' => static::getLang($langKey . '_SETTINGS_NAME_FIELD_USE_BREADCRUMBS_DESCRIPTION', [
					'#PROPERTY_NAME#' => Market\Data\Breadcrumb::PROPERTY_NAME,
					'#PROPERTY_START#' => Market\Data\Breadcrumb::PROPERTY_START,
					'#PROPERTY_EXCLUDE#' => Market\Data\Breadcrumb::PROPERTY_EXCLUDE,
				]),
				'TYPE' => 'boolean',
			];
		}

		return $result;
	}

	protected function getSectionFieldEnum($context)
	{
		try
		{
			$result = [];
			$sectionEntity = Market\Export\Entity\Manager::getSource(Market\Export\Entity\Manager::TYPE_IBLOCK_SECTION);
			$disabledFields = [
				'CODE' => true,
				'XML_ID' => true,
				'DESCRIPTION' => true,
			];

			if ($sectionEntity instanceof Market\Export\Entity\Reference\HasSectionValues)
			{
				foreach ($sectionEntity->getSectionFields($context) as $field)
				{
					if ($field['TYPE'] !== Market\Export\Entity\Data::TYPE_STRING || isset($disabledFields[$field['ID']])) { continue; }

					$result[] = [
						'ID' => $field['ID'],
						'VALUE' => $field['VALUE'],
					];
				}
			}
		}
		catch (Main\SystemException $exception)
		{
			$result = [];
		}

		return $result;
	}
}