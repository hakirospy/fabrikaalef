<?php

namespace Yandex\Market\Trading\Service\Common\Action\OrderStatus;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Action extends TradingService\Common\Action\HttpAction
{
	use Market\Reference\Concerns\HasLang;

	/** @var Request */
	protected $request;
	/** @var TradingEntity\Reference\Order */
	protected $order;
	protected $changes = [];

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	protected function createRequest(Main\HttpRequest $request, Main\Server $server)
	{
		return new Request($request, $server);
	}

	public function getAudit()
	{
		return Market\Logger\Trading\Audit::ORDER_STATUS;
	}

	public function process()
	{
		$this->loadOrder();
		$this->fillStatus();
		$this->fillProperties();

		if ($this->hasChanges())
		{
			$this->updateOrder();
			$this->finalizeStatus();
		}

		$this->response->setRaw('');
	}

	protected function loadOrder()
	{
		$orderRegistry = $this->environment->getOrderRegistry();
		$externalId = $this->request->getOrder()->getId();
		$platform = $this->getPlatform();
		$orderId = $orderRegistry->search($externalId, $platform, false);

		if ($orderId === null)
		{
			$message = static::getLang('TRADING_ACTION_ORDER_STATUS_ORDER_NOT_EXISTS', [
				'#EXTERNAL_ID#' => $externalId,
			]);
			throw new Market\Exceptions\Trading\InvalidOperation($message);
		}

		$this->order = $orderRegistry->loadOrder($orderId);
	}

	protected function fillStatus()
	{
		$statusIn = $this->getStatusIn();

		if ($statusIn !== '')
		{
			$this->saveState();
			$this->setStatus($statusIn);
			$this->pushChange('STATUS', $statusIn);
		}
	}

	protected function finalizeStatus()
	{
		$statusChange = $this->getChange('STATUS');

		if ($statusChange !== null)
		{
			$this->logStatus($statusChange);
			$this->releaseState();
		}
	}

	protected function getStatusIn()
	{
		$result = '';
		$options = $this->provider->getOptions();

		foreach ($this->getStatusInSearchVariants() as $variant)
		{
			$optionValue = (string)$options->getStatusIn($variant);

			if ($optionValue !== '')
			{
				$result = $optionValue;
				break;
			}
		}

		return $result;
	}

	protected function getStatusInSearchVariants()
	{
		return [
			$this->request->getOrder()->getStatus(),
		];
	}

	protected function setStatus($status)
	{
		$subStatus = $this->request->getOrder()->getSubStatus();

		$statusResult = $this->order->setStatus($status, $subStatus);

		Market\Result\Facade::handleException($statusResult);
	}

	protected function fillProperties()
	{
		$values = $this->request->getOrder()->getMeaningfulValues();

		$this->setMeaningfulPropertyValues($values);
	}

	protected function setMeaningfulPropertyValues($values)
	{
		$options = $this->provider->getOptions();
		$personType = $options->getPersonType();
		$values = $this->environment->getProperty()->formatMeaningfulValues($personType, $values);
		$propertyValues = [];

		foreach ($values as $name => $value)
		{
			$propertyId = (string)$options->getProperty($name);

			if ($propertyId !== '')
			{
				$propertyValues[$propertyId] = $value;
			}
		}

		if (!empty($propertyValues))
		{
			$fillResult = $this->order->fillProperties($propertyValues);
			$fillData = $fillResult->getData();

			if (!empty($fillData['CHANGES']))
			{
				$this->pushChange('PROPERTIES', $fillData['CHANGES']);
			}
		}
	}

	protected function updateOrder()
	{
		$updateResult = $this->order->update();

		Market\Result\Facade::handleException($updateResult);
	}

	protected function logStatus($status)
	{
		$logger = $this->provider->getLogger();
		$message = static::getLang('TRADING_ACTION_ORDER_STATUS_LOG', [
			'#STATUS#' => $status,
			'#SUBSTATUS#' => $this->request->getOrder()->getSubStatus(),
		]);

		$logger->info($message, [
			'AUDIT' => $this->getAudit(),
			'ENTITY_TYPE' => TradingEntity\Registry::ENTITY_TYPE_ORDER,
			'ENTITY_ID' => $this->order->getAccountNumber(),
		]);
	}

	protected function saveState()
	{
		$serviceKey = $this->provider->getUniqueKey();
		$orderId = $this->request->getOrder()->getId();
		$fullStatus = [
			$this->request->getOrder()->getStatus(),
			$this->request->getOrder()->getSubStatus(),
		];

		Market\Trading\State\OrderStatus::setValue($serviceKey, $orderId, implode(':', $fullStatus));
	}

	protected function releaseState()
	{
		$serviceKey = $this->provider->getUniqueKey();
		$orderId = $this->request->getOrder()->getId();

		Market\Trading\State\OrderStatus::releaseValue($serviceKey, $orderId);
	}

	protected function pushChange($key, $value)
	{
		$this->changes[$key] = $value;
	}

	protected function hasChanges()
	{
		return !empty($this->changes);
	}

	protected function getChange($key)
	{
		return isset($this->changes[$key]) ? $this->changes[$key] : null;
	}
}