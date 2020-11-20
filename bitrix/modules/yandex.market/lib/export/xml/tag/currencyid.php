<?php

namespace Yandex\Market\Export\Xml\Tag;

use Yandex\Market;
use Bitrix\Main;

class CurrencyId extends Base
{
	public function getDefaultParameters()
	{
		return [
			'name' => 'currencyId',
			'value_type' => Market\Type\Manager::TYPE_CURRENCY
		];
	}

	public function getSourceRecommendation(array $context = [])
	{
		if ($context['HAS_CATALOG'])
		{
			$exportService = isset($context['EXPORT_SERVICE']) ? $context['EXPORT_SERVICE'] : null;

			if ($exportService === Market\Export\Xml\Format\Manager::EXPORT_SERVICE_TURBO)
			{
				$result = array_merge(
					$this->getSiteCurrencyRecommendation(),
					$this->getCatalogPriceRecommendation()
				);
			}
			else
			{
				$result = array_merge(
					$this->getCatalogPriceRecommendation(),
					$this->getSiteCurrencyRecommendation()
				);
			}
		}
		else
		{
			$result = $this->getTextRecommendation();
		}

		return $result;
	}

	protected function getSiteCurrencyRecommendation()
	{
		$result = [];

		if (Main\ModuleManager::isModuleInstalled('currency'))
		{
			$result[] = [
				'TYPE' => Market\Export\Entity\Manager::TYPE_CURRENCY,
				'FIELD' => Market\Data\Currency::getBaseCurrency(),
			];
		}

		return $result;
	}

	protected function getCatalogPriceRecommendation()
	{
		return [
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_CATALOG_PRICE,
				'FIELD' => 'MINIMAL.CURRENCY'
			],
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_CATALOG_PRICE,
				'FIELD' => 'OPTIMAL.CURRENCY'
			],
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_CATALOG_PRICE,
				'FIELD' => 'BASE.CURRENCY'
			],
		];
	}

	protected function getTextRecommendation()
	{
		return [
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_TEXT,
				'VALUE' => 'RUR'
			]
		];
	}
}