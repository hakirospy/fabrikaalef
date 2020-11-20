<?php

namespace Yandex\Market\Trading\Service\Common\Action\OrderAccept;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Action extends TradingService\Common\Action\Cart\Action
{
	/** @var Request */
	protected $request;
	/** @var TradingEntity\Reference\User */
	protected $originalUser;
	/** @var array */
	protected $relatedProperties = [];

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
		parent::includeMessages();
	}

	protected function createRequest(Main\HttpRequest $request, Main\Server $server)
	{
		return new Request($request, $server);
	}

	public function getAudit()
	{
		return Market\Logger\Trading\Audit::ORDER_ACCEPT;
	}

	public function process()
	{
		$orderId = $this->searchOrder();

		if ($orderId !== null)
		{
			$this->loadOrder($orderId);

			$orderNum = $this->getOrderNum();
			$hasWarnings = $this->isExistOrderMarker();

			$this->collectOrder($orderNum, $hasWarnings);
		}
		else
		{
			$this->createUser();
			$this->createOrder();
			$this->initializeOrder();
			$this->fillOrder();
			$this->finalizeOrder();

			$checkResult = $this->check();

			if ($checkResult->isSuccess())
			{
				$hasWarnings = false;

				if ($checkResult->hasWarnings())
				{
					$hasWarnings = true;
					$this->markOrder($checkResult);
				}

				$this->addOrder();

				$orderNum = $this->getOrderNum();

				$this->logOrder($orderNum);
				$this->collectOrder($orderNum, $hasWarnings);
			}
			else
			{
				$this->collectDecline($checkResult);
				$this->logDecline($checkResult);
			}
		}
	}

	protected function searchOrder()
	{
		$orderRegistry = $this->environment->getOrderRegistry();
		$orderId = $this->request->getOrder()->getId();
		$platform = $this->getPlatform();

		return $orderRegistry->search($orderId, $platform, false);
	}

	protected function loadOrder($orderId)
	{
		$orderRegistry = $this->environment->getOrderRegistry();

		$this->order = $orderRegistry->loadOrder($orderId);
	}

	protected function fillOrder()
	{
		$this->fillXmlId();
		$this->fillStatus();
		$this->fillProfile();
		$this->fillRegion();
		$this->fillProperties();
		$this->fillBasket();
		$this->fillDelivery();
		$this->fillPaySystem();
		$this->fillRelatedProperties();
		$this->fillNotes();
	}

	protected function fillXmlId()
	{
		$platform = $this->getPlatform();
		$externalId = $this->request->getOrder()->getId();

		$this->order->fillXmlId($externalId, $platform);
	}

	protected function fillProperties()
	{
		$this->fillUtilProperties();
	}

	protected function fillRelatedProperties()
	{
		if (!empty($this->relatedProperties))
		{
			$this->order->fillProperties($this->relatedProperties);
			$this->relatedProperties = [];
		}
	}

	protected function fillUtilProperties()
	{
		$meaningfulValues = $this->request->getOrder()->getMeaningfulValues();

		$this->setMeaningfulPropertyValues($meaningfulValues);
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

			if (isset($fillData['FILLED']))
			{
				$filledMap = array_fill_keys((array)$fillData['FILLED'], true);
				$this->relatedProperties += array_diff_key($propertyValues, $filledMap);
			}
		}
	}

	protected function fillDelivery()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'fillDelivery');
	}

	protected function fillPaySystem()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'fillPaySystem');
	}

	protected function fillNotes()
	{
		$notes = $this->request->getOrder()->getNotes();

		if ($notes !== '')
		{
			$this->order->setNotes($notes);
		}
	}

	protected function fillStatus()
	{
		/** @var TradingService\Common\Options $options */
		$options = $this->provider->getOptions();
		$status = (string)$options->getStatusIn(TradingService\Common\Status::VIRTUAL_CREATED);

		if ($status !== '')
		{
			$this->order->setStatus($status);
		}
	}

	protected function check()
	{
		return Market\Result\Facade::merge([
			$this->checkBasket(),
			$this->checkPrice(),
		]);
	}

	protected function checkBasket()
	{
		$validationResult = $this->validateBasket();
		$result = new Market\Result\Base();

		if (!$validationResult->isSuccess())
		{
			$isAllowModify = $this->provider->getOptions()->isAllowModifyBasket();

			if ($isAllowModify)
			{
				$modifyResult = $this->modifyBasket();

				if ($modifyResult->isSuccess())
				{
					$result->addWarnings($validationResult->getErrors());
				}
				else
				{
					$result->addErrors($modifyResult->getErrors());
				}
			}
			else
			{
				$result->addErrors($validationResult->getErrors());
			}
		}

		return $result;
	}

	protected function modifyBasket()
	{
		$items = $this->request->getOrder()->getItems();
		$result = new Main\Result();

		/** @var Market\Api\Model\Order\Item $item */
		foreach ($items as $itemIndex => $item)
		{
			$offerId = $item->getOfferId();
			$count = $item->getCount();
			$basketCode = null;

			if (isset($this->basketMap[$itemIndex]))
			{
				$basketCode = $this->basketMap[$itemIndex];
			}
			else
			{
				$productId = isset($this->basketInvalidProducts[$itemIndex])
					? $this->basketInvalidProducts[$itemIndex]
					: $offerId;
				$basketData = isset($this->basketInvalidData[$itemIndex])
					? $this->basketInvalidData[$itemIndex]
					: [];
				$basketData += $item->getMeaningfulValues();
				$basketData = array_diff_key($basketData, [ 'ERROR' => true ]);

				$addResult = $this->order->addProduct($productId, 0, $basketData);
				$addData = $addResult->getData();

				if (isset($addData['BASKET_CODE']))
				{
					$basketCode = $addData['BASKET_CODE'];
					$this->basketMap[$itemIndex] = $basketCode;
				}
			}

			if ($basketCode !== null)
			{
				$basketResult = $this->order->setBasketItemQuantity($basketCode, $count, true);

				if (!$basketResult->isSuccess())
				{
					$result->addErrors($basketResult->getErrors());
				}
			}
		}

		return $result;
	}

	protected function checkPrice()
	{
		$validationResult = $this->validatePrice();
		$result = new Market\Result\Base();

		if (!$validationResult->isSuccess())
		{
			$allowModifyPrice = $this->provider->getOptions()->isAllowModifyPrice();
			$checkPriceData = $validationResult->getData();

			if ($checkPriceData['SIGN'] > 0) // requested price more then basket price
			{
				$allowModifyPrice = true;
			}

			if ($allowModifyPrice)
			{
				$modifyPrice = $this->modifyPrice();

				if (!$modifyPrice->isSuccess())
				{
					$result->addErrors($modifyPrice->getErrors());
				}
			}
			else
			{
				$result->addErrors($validationResult->getErrors());
			}
		}

		return $result;
	}

	protected function validatePrice()
	{
		$items = $this->request->getOrder()->getItems();
		$requestPrice = $items->getSum();
		$basketPrice = $this->order->getBasketPrice();
		$result = new Market\Result\Base();

		if (Market\Data\Price::round($requestPrice) !== Market\Data\Price::round($basketPrice))
		{
			$currency = $this->order->getCurrency();

			$message = static::getLang('TRADING_ACTION_ORDER_ACCEPT_ORDER_PRICE_NOT_MATCH', [
				'#REQUEST_PRICE#' => Market\Data\Currency::format($requestPrice, $currency),
				'#BASKET_PRICE#' => Market\Data\Currency::format($basketPrice, $currency),
			]);
			$result->addError(new Market\Error\Base($message, 'PRICE_NOT_MATCH'));
			$result->setData([
				'SIGN' => $requestPrice < $basketPrice ? -1 : 1,
			]);
		}

		return $result;
	}

	protected function modifyPrice()
	{
		$items = $this->request->getOrder()->getItems();
		$result = new Market\Result\Base();

		/** @var Market\Api\Model\Order\Item $item */
		foreach ($items as $itemIndex => $item)
		{
			if (isset($this->basketMap[$itemIndex]))
			{
				$basketCode = $this->basketMap[$itemIndex];
				$price = $item->getFullPrice();
				$basketResult = $this->order->setBasketItemPrice($basketCode, $price);

				if (!$basketResult->isSuccess())
				{
					$result->addErrors($basketResult->getErrors());
				}
			}
		}

		return $result;
	}

	protected function isExistOrderMarker()
	{
		$codePrefix = $this->getMarkerPrefix();

		return $this->order->isExistMarker($codePrefix, '%');
	}

	protected function markOrder(Market\Result\Base $checkResult)
	{
		$codePrefix = $this->getMarkerPrefix();

		foreach ($checkResult->getWarnings() as $warning)
		{
			$message = $warning->getMessage();
			$code = $codePrefix . $warning->getCode();

			$addResult = $this->order->addMarker($message, $code);

			Market\Result\Facade::handleException($addResult);
		}
	}

	protected function getMarkerPrefix()
	{
		$serviceCode = $this->provider->getCode();

		return 'YAMARKET_' . strtoupper($serviceCode) . '_';
	}

	protected function addOrder()
	{
		$platform = $this->getPlatform();
		$externalId = $this->request->getOrder()->getId();
		$saveResult = $this->order->add($externalId, $platform);
		$saveData = $saveResult->getData();

		if (!$saveResult->isSuccess())
		{
			$errorMessage = implode(PHP_EOL, $saveResult->getErrorMessages());
			throw new Main\SystemException($errorMessage);
		}

		if (!isset($saveData['ID']))
		{
			$errorMessage = static::getLang('TRADING_ACTION_ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			throw new Main\SystemException($errorMessage);
		}
	}

	protected function getOrderNum()
	{
		return $this->order->getAccountNumber();
	}

	protected function logOrder($orderNum)
	{
		$logger = $this->provider->getLogger();
		$message = static::getLang('TRADING_ACTION_ORDER_ACCEPT_SAVE_LOG', [
			'#ORDER_ID#' => $orderNum,
			'#EXTERNAL_ID#' => $this->request->getOrder()->getId(),
		]);

		$logger->info($message, [
			'ENTITY_TYPE' => TradingEntity\Registry::ENTITY_TYPE_ORDER,
			'ENTITY_ID' => $orderNum,
		]);
	}

	protected function collectOrder($orderNum, $hasWarnings = false)
	{
		$this->response->setField('order.id', (string)$orderNum);
		$this->response->setField('order.accepted', true);

		if ($hasWarnings)
		{
			$this->response->setField('order.subscribe', (bool)$hasWarnings);
		}
	}

	protected function logDecline(Market\Result\Base $result)
	{
		$logger = $this->provider->getLogger();
		$message = implode(PHP_EOL, $result->getErrorMessages());

		$logger->error($message);
	}

	protected function collectDecline(Market\Result\Base $result)
	{
		$this->response->setField('order.accepted', false);
		$this->response->setField('order.reason', 'OUT_OF_DATE');
	}
}