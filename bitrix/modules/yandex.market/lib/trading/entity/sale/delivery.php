<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Sale;

class Delivery extends Market\Trading\Entity\Reference\Delivery
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
		$deliveries = $this->loadActiveList();
		$deliveries = $this->filterBySite($deliveries, $siteId);

		return $deliveries;
	}

	protected function loadActiveList()
	{
		$result = [];

		foreach (Sale\Delivery\Services\Manager::getActiveList(true) as $id => $fields)
		{
			if ($delivery = Sale\Delivery\Services\Manager::createObject($fields))
			{
				$result[] = [
					'ID' => $id,
					'VALUE' => $delivery->getNameWithParent(),
					'TYPE' => $this->getDeliveryServiceType($delivery),
				];
			}
		}

		return $result;
	}

	protected function getDeliveryServiceType(Sale\Delivery\Services\Base $deliveryService)
	{
		if ((int)$deliveryService->getId() === (int)Sale\Delivery\Services\Manager::getEmptyDeliveryServiceId())
		{
			$result = Market\Data\Trading\Delivery::EMPTY_DELIVERY;
		}
		else
		{
			$result = null;
		}

		return $result;
	}

	protected function filterBySite($deliveryServices, $siteId)
	{
		$result = [];

		if ($siteId === null)
		{
			$result = $deliveryServices;
		}
		else if (!empty($deliveryServices))
		{
			$deliveryIds = array_column($deliveryServices, 'ID');

			if (count($deliveryIds) === 1) // if only one then result boolean
			{
				$deliveryIds[] = -1;
			}

			$validServices = Sale\Delivery\Services\Manager::checkServiceRestriction(
				$deliveryIds,
				$siteId,
				'\Bitrix\Sale\Delivery\Restrictions\BySite'
			);

			if (is_array($validServices))
			{
				$validServicesMap = array_flip($validServices);
			}
			else // is older version
			{
				$validServicesMap = [];

				foreach ($deliveryServices as $delivery)
				{
					$isValid = Sale\Delivery\Services\Manager::checkServiceRestriction(
						$delivery['ID'],
						$siteId,
						'\Bitrix\Sale\Delivery\Restrictions\BySite'
					);

					if ($isValid)
					{
						$validServicesMap[$delivery['ID']] = true;
					}
				}
			}

			foreach ($deliveryServices as $deliveryService)
			{
				if (isset($validServicesMap[$deliveryService['ID']]))
				{
					$result[] = $deliveryService;
				}
			}
		}

		return $result;
	}
}