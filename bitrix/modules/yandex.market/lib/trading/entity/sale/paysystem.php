<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Sale;

class PaySystem extends Market\Trading\Entity\Reference\PaySystem
{
	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function isRequired()
	{
		$saleVersion = Main\ModuleManager::getVersion('sale');

		return !CheckVersion($saleVersion, '17.0.0');
	}

	public function getEnum($siteId = null)
	{
		$result = [];

		$query = Sale\PaySystem\Manager::getList([
			'filter' => ['=ACTIVE' => 'Y'],
			'order' => ['SORT' => 'ASC', 'NAME' => 'ASC'],
			'select' => ['ID', 'NAME']
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => $row['NAME'],
			];
		}

		return $result;
	}
}