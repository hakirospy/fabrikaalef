<?php

namespace Yandex\Market\Trading\Service\Marketplace\Action\AdminView;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Action extends TradingService\Reference\Action\DataAction
{
	use Market\Reference\Concerns\HasLang;

	/** @var TradingService\Marketplace\Provider */
	protected $provider;
	/** @var Request */
	protected $request;
	/** @var Market\Api\Model\Order */
	protected $externalOrder;
	/** @var Market\Trading\Entity\Reference\Order */
	protected $bitrixOrder;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(TradingService\Marketplace\Provider $provider, TradingEntity\Reference\Environment $environment, array $data)
	{
		parent::__construct($provider, $environment, $data);
	}

	protected function createRequest(array $data)
	{
		return new Request($data);
	}

	public function process()
	{
		$this->loadBitrixOrder();
		$this->checkViewAccess();

		$this->loadExternalOrder();

		$this->collectProperties();
		$this->collectBasketColumns();
		$this->collectBasketItems();
		$this->collectBasketSummary();
		$this->collectShipments();
		$this->collectShipmentEdit();
		$this->collectPrintReady();
	}

	protected function loadBitrixOrder()
	{
		$externalId = $this->request->getOrderId();
		$orderRegistry = $this->environment->getOrderRegistry();
		$platform = $this->getPlatform();
		$bitrixId = $orderRegistry->search($externalId, $platform, false);

		if ($bitrixId === null)
		{
			$message = static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_ORDER_NOT_EXISTS', [
				'#EXTERNAL_ID#' => $externalId,
			]);
			throw new Market\Exceptions\Trading\InvalidOperation($message);
		}

		$this->bitrixOrder = $orderRegistry->loadOrder($bitrixId);
	}

	protected function checkViewAccess()
	{
		if (!$this->hasRights(TradingEntity\Operation\Order::VIEW))
		{
			throw new Main\AccessDeniedException();
		}
	}

	protected function loadExternalOrder()
	{
		$orderId = $this->request->getOrderId();

		$this->flushCache();
		$this->fetchOrderByPrimary($orderId);
		$this->writeCache();
	}

	protected function fetchOrderByPrimary($primary)
	{
		$useCache = $this->request->useCache();

		if ($useCache && Market\Trading\State\SessionCache::has('order', $primary))
		{
			$fields = Market\Trading\State\SessionCache::get('order', $primary);
			$result = TradingService\Marketplace\Model\Order::initialize($fields);
		}
		else
		{
			$options = $this->provider->getOptions();
			$logger = $this->provider->getLogger();

			$result = TradingService\Marketplace\Model\OrderFacade::load($options, $primary, $logger);
		}

		$this->externalOrder = $result;
	}

	protected function flushCache()
	{
		if ($this->request->flushCache())
		{
			Market\Trading\State\SessionCache::releaseByType('order');
		}
	}

	protected function writeCache()
	{
		if ($this->request->useCache())
		{
			Market\Trading\State\SessionCache::set(
				'order',
				$this->externalOrder->getId(),
				$this->externalOrder->getFields()
			);
		}
	}

	protected function collectProperties()
	{
		foreach ($this->getPropertyFields() as $propertyName)
		{
			$propertyValue = $this->getPropertyValue($propertyName);
			$formattedValue = $propertyValue !== null
				? (string)$this->formatPropertyValue($propertyName, $propertyValue)
				: '';

			if ($formattedValue !== '')
			{
				$this->response->pushField('properties', [
					'ID' => $propertyName,
					'NAME' => $this->getPropertyTitle($propertyName),
					'VALUE' => $formattedValue,
				]);
			}
		}
	}

	protected function getPropertyFields()
	{
		$result = [
			'creationDate',
			'shipmentDate',
			'fake',
			'cancelRequested',
			'status',
			'substatus',
			'paymentType',
			'paymentMethod',
			'notes',
		];

		if (Market\Config::isExpertMode())
		{
			array_splice($result, -1, 0, [
				'taxSystem',
			]);
		}

		return $result;
	}

	protected function getPropertyTitle($propertyName)
	{
		return static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_PROPERTY_' . strtoupper($propertyName), null, $propertyName);
	}

	protected function getPropertyValue($propertyName)
	{
		switch ($propertyName)
		{
			case 'shipmentDate':
				$result = [];

				if ($this->externalOrder->hasDelivery())
				{
					/** @var Market\Api\Model\Order\Shipment $shipment */
					foreach ($this->externalOrder->getDelivery()->getShipments() as $shipment)
					{
						if ($shipment->hasField('shipmentDate'))
						{
							$result[] = $shipment->getField('shipmentDate');
						}
					}
				}
			break;

			default:
				$result = $this->externalOrder->getField($propertyName);
			break;
		}

		return $result;
	}

	protected function formatPropertyValue($propertyName, $propertyValue)
	{
		switch ($propertyName)
		{
			case 'fake':
			case 'cancelRequested':
				$result = (int)$propertyValue > 0
					? static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_BOOLEAN_YES')
					: static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_BOOLEAN_NO');
				break;

			case 'status':
			case 'substatus':
				$result = $this->provider->getStatus()->getTitle($propertyValue);
			break;

			case 'taxSystem':
				$result = $this->provider->getTaxSystem()->getTypeTitle($propertyValue);
			break;

			case 'paymentType':
				$result = $this->provider->getPaySystem()->getTypeTitle($propertyValue);
			break;

			case 'paymentMethod':
				$result = $this->provider->getPaySystem()->getMethodTitle($propertyValue);
			break;

			default:
				$result = is_array($propertyValue) ? implode(', ', $propertyValue) : $propertyValue;
			break;
		}

		return $result;
	}

	protected function collectBasketColumns()
	{
		$columns = [];

		foreach ($this->getBasketColumns() as $column)
		{
			$columns[$column] = static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_BASKET_' . $column);
		}

		$this->response->setField('basket.columns', $columns);
	}

	protected function getBasketColumns()
	{
		$result = [
			'NAME',
			'PRICE',
			'SUBSIDY',
			'COUNT',
		];

		if (Market\Config::isExpertMode())
		{
			$result[] = 'VAT';
		}

		return $result;
	}

	protected function collectBasketItems()
	{
		$currency = $this->externalOrder->getCurrency();
		$isConfirmed = $this->isOrderConfirmed();

		/** @var TradingService\Marketplace\Model\Order\Item $item */
		foreach ($this->externalOrder->getItems() as $item)
		{
			$basketItem = [
				'ID' => $item->getId(),
				'OFFER_ID' => $item->getOfferId(),
				'NAME' => $item->getOfferName(),
				'COUNT' => $item->getCount(),
				'PRICE' => null,
				'PRICE_FORMATTED' => null,
				'CURRENCY' => $currency,
				'SUBSIDY' => null,
				'SUBSIDY_FORMATTED' => null,
				'PROMOS' => [],
				'VAT' => $item->getVat(),
				'VAT_FORMATTED' => Market\Data\Vat::getTitle($item->getVat()),
			];

			if ($isConfirmed)
			{
				$basketItem['PRICE'] = $item->getFullPrice();
				$basketItem['PRICE_FORMATTED'] = Market\Data\Currency::format($item->getFullPrice(), $currency);

				$subsidy = $item->getSubsidy();

				if ($subsidy > 0)
				{
					$basketItem['SUBSIDY'] = $subsidy;
					$basketItem['SUBSIDY_FORMATTED'] = Market\Data\Currency::format($subsidy, $currency);
				}

				$promos = $item->getPromos();

				if ($promos !== null)
				{
					$basketItem['PROMOS'] = $this->getItemPromosSummary($promos);
				}
			}

			$this->response->pushField('basket.items', $basketItem);
		}
	}

	protected function getItemPromosSummary(TradingService\Marketplace\Model\Order\Item\PromoCollection $promoCollection)
	{
		$promoEntity = $this->provider->getPromo();
		$visibleTypes = $promoEntity->getVisibleTypes();
		$result = [];

		/** @var TradingService\Marketplace\Model\Order\Item\Promo $promo*/
		foreach ($promoCollection as $promo)
		{
			if (!in_array($promo->getType(), $visibleTypes, true)) { continue; }

			$type = $promo->getType();
			$shopPromoId = $promo->getShopPromoId();

			$promoText = $promoEntity->getTitle($type);

			if ($shopPromoId !== null)
			{
				$promoText .= sprintf(' #%s', $shopPromoId);
			}

			$result[] = $promoText;
		}

		return $result;
	}

	protected function collectBasketSummary()
	{
		if (!$this->isOrderConfirmed()) { return; }

		foreach ($this->getBasketSummaryValues() as $key => $value)
		{
			$this->response->pushField('basket.summary', [
				'NAME' => static::getLang('TRADING_MARKETPLACE_ORDER_VIEW_BASKET_SUMMARY_' . $key),
				'VALUE' => $value,
			]);
		}
	}

	protected function getBasketSummaryValues()
	{
		$currency = $this->externalOrder->getCurrency();
		$values = [];
		$itemsTotal = $this->externalOrder->getItemsTotal();
		$subsidyTotal = $this->externalOrder->getSubsidyTotal();

		if ($this->externalOrder->getSubsidyTotal() > 0)
		{
			$itemsTotalWithSubsidy = $itemsTotal + $subsidyTotal;

			$values['ITEMS_TOTAL_WITH_SUBSIDY'] = Market\Data\Currency::format($itemsTotalWithSubsidy, $currency);
			$values['SUBSIDY_TOTAL'] = Market\Data\Currency::format($subsidyTotal, $currency);
			$values['ITEMS_TOTAL'] = Market\Data\Currency::format($itemsTotal, $currency);
		}
		else
		{

			$values['ITEMS_TOTAL'] = Market\Data\Currency::format($itemsTotal, $currency);
		}

		return $values;
	}

	protected function collectShipments()
	{
		$delivery = $this->externalOrder->getDelivery();

		foreach ($delivery->getShipments() as $shipment)
		{
			$this->response->pushField('shipments', [
				'ID' => $shipment->getId(),
				'BOX' => $this->getShipmentBoxes($shipment),
			]);
		}
	}

	protected function getShipmentBoxes(Market\Api\Model\Order\Shipment $shipment)
	{
		$result = [];

		/** @var Market\Api\Model\Order\Box $box*/
		foreach ($shipment->getBoxes() as $box)
		{
			$result[] = [
				'ID' => $box->getId(),
				'DIMENSIONS' => $this->getShipmentBoxDimensions($box),
			];
		}

		return $result;
	}

	protected function getShipmentBoxDimensions(Market\Api\Model\Order\Box $box)
	{
		$result = [];

		// weight

		$weightUnit = $box->getWeightUnit();

		$result['WEIGHT'] = [
			'VALUE' => $box->getWeight(),
			'UNIT' => $weightUnit,
		];

		// sizes

		$sizes = [
			'WIDTH' => $box->getWidth(),
			'HEIGHT' => $box->getHeight(),
			'DEPTH' => $box->getDepth(),
		];
		$sizeUnit = $box->getSizeUnit();

		foreach ($sizes as $sizeName => $sizeValue)
		{
			$result[$sizeName] = [
				'VALUE' => $sizeValue,
				'UNIT' => $sizeUnit,
			];
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @param Market\Api\Model\Order\Box $box
	 *
	 * @return array
	 */
	protected function getShipmentBoxItems(Market\Api\Model\Order\Box $box)
	{
		$result = [];

		/** @var Market\Api\Model\Order\BoxItem $boxItem*/
		foreach ($box->getItems() as $boxItem)
		{
			$result[] = [
				'ID' => $boxItem->getId(),
				'COUNT' => $boxItem->getCount(),
			];
		}

		return $result;
	}

	protected function collectShipmentEdit()
	{
		$allowEdit = (
			$this->isOrderProcessing()
			&& $this->hasRights(TradingEntity\Operation\Order::BOX)
		);

		$this->response->setField('shipmentEdit', $allowEdit);
	}

	protected function collectPrintReady()
	{
		$result = $this->isOrderProcessing() && $this->hasSavedBoxes();

		$this->response->setField('printReady', $result);
	}

	protected function isOrderConfirmed()
	{
		$status = $this->externalOrder->getStatus();

		return $this->provider->getStatus()->isConfirmed($status);
	}

	protected function isOrderProcessing()
	{
		$status = $this->externalOrder->getStatus();

		return $this->provider->getStatus()->isProcessing($status);
	}

	protected function hasSavedBoxes()
	{
		$result = false;

		if ($this->externalOrder->hasDelivery())
		{
			/** @var Market\Api\Model\Order\Shipment $shipment */
			foreach ($this->externalOrder->getDelivery()->getShipments() as $shipment)
			{
				if ($shipment->hasSavedBoxes())
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	protected function hasRights($operation)
	{
		if (!$this->request->needCheckAccess())
		{
			$result = true;
		}
		else
		{
			$userId = $this->request->getUserId();
			$result = $this->bitrixOrder->hasAccess($userId, $operation);
		}

		return $result;
	}
}