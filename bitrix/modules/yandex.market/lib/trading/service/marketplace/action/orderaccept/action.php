<?php

namespace Yandex\Market\Trading\Service\Marketplace\Action\OrderAccept;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Action extends TradingService\Common\Action\OrderAccept\Action
{
	/** @var Request */
	protected $request;

	protected function createRequest(Main\HttpRequest $request, Main\Server $server)
	{
		return new Request($request, $server);
	}

	protected function fillDelivery()
	{
		$deliveryId = $this->provider->getOptions()->getDeliveryId();

		if ($deliveryId !== '')
		{
			$this->order->createShipment($deliveryId);
		}
	}

	protected function fillPaySystem()
	{
		$paySystemId = (string)$this->provider->getOptions()->getPaySystemId();

		if ($paySystemId !== '')
		{
			$this->order->createPayment($paySystemId);
		}
	}
}