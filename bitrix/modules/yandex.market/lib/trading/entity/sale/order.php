<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Yandex\Market\Trading\Entity\Reference as EntityReference;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

class Order extends Market\Trading\Entity\Reference\Order
{
	use Market\Reference\Concerns\HasLang;

	/** @var int|null */
	protected $eventProcessing;
	/** @var array */
	protected $initialChangedValues;
	/** @var bool|null*/
	protected $isStartField;
	/** @var Environment */
	protected $environment;
	/** @var Sale\OrderBase*/
	protected $internalOrder;
	/** @var Internals\BasketDataPreserver */
	protected $basketDataPreserver;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Environment $environment, Sale\OrderBase $internalOrder, $eventProcessing = null)
	{
		parent::__construct($environment, $internalOrder);

		$this->eventProcessing = $eventProcessing;
		$this->initialChangedValues = $eventProcessing !== null ? $internalOrder->getFields()->getChangedValues() : [];
	}

	public function getAdminEditUrl()
	{
		return Market\Ui\Admin\Path::getPageUrl('sale_order_view', [
			'ID' => $this->getId(),
			'lang' => LANGUAGE_ID,
			'sale_order_view_active_tab' => 'YANDEX_MARKET_TRADING_ORDER_VIEW',
		]);
	}

	public function hasAccess($userId, $operation)
	{
		switch ($operation)
		{
			case Market\Trading\Entity\Operation\Order::VIEW:
				$result = $this->hasStatusRights($userId, 'view');
			break;

			case Market\Trading\Entity\Operation\Order::BOX:
				$result =
					$this->hasStatusRights($userId, 'update')
					|| $this->hasShipmentRights($userId, ['update', 'delivery', 'deduction']);
			break;

			default:
				throw new Main\ArgumentException('unknown operation');
			break;
		}

		return $result;
	}

	protected function hasStatusRights($userId, $saleActions)
	{
		/** @var OrderRegistry $orderRegistry */
		$orderRegistry = $this->environment->getOrderRegistry();
		$statusClass = $orderRegistry::getOrderStatusClassName();
		$status = $this->internalOrder->getField('STATUS_ID');
		$result = false;

		foreach ((array)$saleActions as $saleAction)
		{
			$allowedStatuses = $statusClass::getStatusesUserCanDoOperations($userId, [ $saleAction ]);

			if (in_array($status, $allowedStatuses, true))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected function hasShipmentRights($userId, $saleActions)
	{
		/** @var OrderRegistry $orderRegistry */
		$shipment = $this->getNotSystemShipment();

		if ($shipment !== null)
		{
			$orderRegistry = $this->environment->getOrderRegistry();
			$statusClass = $orderRegistry::getDeliveryStatusClassName();
			$status = $shipment->getField('STATUS_ID');
			$result = false;

			foreach ((array)$saleActions as $saleAction)
			{
				$allowedStatuses = $statusClass::getStatusesUserCanDoOperations($userId, [ $saleAction ]);

				if (in_array($status, $allowedStatuses, true))
				{
					$result = true;
					break;
				}
			}
		}
		else
		{
			$result = false;
		}

		return $result;
	}

	public function getId()
	{
		return $this->internalOrder->getId();
	}

	public function getAccountNumber()
	{
		return OrderRegistry::getOrderAccountNumber($this->internalOrder);
	}

	public function getCurrency()
	{
		return $this->internalOrder->getCurrency();
	}

	public function getSiteId()
	{
		return $this->internalOrder->getSiteId();
	}

	public function setUserId($userId)
	{
		$userId = (int)$userId;
		$result = new Main\Result();

		if ($this->getId() > 0)
		{
			$result->addError(new Main\Error('cant change userId for saved order'));
		}
		else if ($userId <= 0)
		{
			$result->addError(new Main\Error('cant set empty userId'));
		}
		else
		{
			$basket = $this->internalOrder->getBasket();

			$this->internalOrder->setFieldNoDemand('USER_ID', $userId);

			if ($basket)
			{
				$fuserId = Sale\Fuser::getIdByUserId($userId);
				$basket->setFUserId($fuserId);
			}
		}

		return $result;
	}

	public function setPersonType($personType)
	{
		return $this->internalOrder->setPersonTypeId($personType);
	}

	public function initialize()
	{
		Sale\DiscountCouponsManager::init(Sale\DiscountCouponsManager::MODE_EXTERNAL);

		$this->isStartField = $this->internalOrder->isStartField();

		$this->getBasket(); // initialize basket (fix clear shipmentCollection)
	}

	public function fillXmlId($externalId, EntityReference\Platform $platform)
	{
		$xmlId = $platform->getOrderXmlId($externalId);

		$this->internalOrder->setField('XML_ID', $xmlId);
	}

	public function fillProperties(array $values)
	{
		$propertyCollection = $this->internalOrder->getPropertyCollection();
		$result = new Main\Result();
		$changes = [];
		$filled = [];

		/** @var Sale\PropertyValue $property*/
		foreach ($propertyCollection as $property)
		{
			$propertyId = $property->getPropertyId();

			if ($propertyId === null || !array_key_exists($propertyId, $values)) { continue; }

			$value = $values[$propertyId];
			$sanitizedValue = $this->sanitizePropertyValue($property, $value);

			$property->setValue($sanitizedValue);

			if ($property->isChanged())
			{
				$changes[] = $propertyId;
			}

			$filled[] = $propertyId;
		}

		$result->setData([
			'CHANGES' => $changes,
			'FILLED' => $filled,
		]);

		return $result;
	}

	protected function sanitizePropertyValue(Sale\PropertyValue $property, $value)
	{
		$value = $this->sanitizePropertyValueByType($property, $value);
		$value = $this->sanitizePropertyValueMultiple($property, $value);

		return $value;
	}

	protected function sanitizePropertyValueByType(Sale\PropertyValue $property, $value)
	{
		$propertyRow = $property->getProperty();
		$propertyType = isset($propertyRow['TYPE']) ? $propertyRow['TYPE'] : 'STRING';

		if ($propertyType === 'DATE')
		{
			$isValueMultiple = is_array($value);
			$dates = is_array($value) ? $value : [ $value ];

			foreach ($dates as &$date)
			{
				if ($date instanceof Main\Type\DateTime)
				{
					$date = ConvertTimeStamp($date->getTimestamp(), 'FULL');
				}
				else if ($date instanceof Main\Type\Date)
				{
					$date = ConvertTimeStamp($date->getTimestamp(), 'SHORT');
				}
			}
			unset($date);

			$result = $isValueMultiple ? $dates : reset($dates);
		}
		else
		{
			$result = $value;
		}

		return $result;
	}

	protected function sanitizePropertyValueMultiple(Sale\PropertyValue $property, $value)
	{
		$propertyRow = $property->getProperty();
		$isPropertyMultiple = (isset($propertyRow['MULTIPLE']) && $propertyRow['MULTIPLE'] === 'Y');
		$isValueMultiple = is_array($value);

		if ($isPropertyMultiple === $isValueMultiple)
		{
			$result = $value;
		}
		else if ($isValueMultiple)
		{
			$result = reset($value);
		}
		else if (!Market\Utils\Value::isEmpty($value))
		{
			$result = [ $value ];
		}
		else
		{
			$result = null;
		}

		return $result;
	}

	public function setLocation($locationId)
	{
		$propertyCollection = $this->internalOrder->getPropertyCollection();
		$locationProperty = $propertyCollection->getDeliveryLocation();

		if ($locationProperty !== null)
		{
			$locationCode = \CSaleLocation::getLocationCODEbyID($locationId);
			$result = $locationProperty->setValue($locationCode);
		}
		else
		{
			$result = new Main\Result();

			$errorMessage = static::getLang('TRADING_ENTITY_SALE_ORDER_HASNT_LOCATION_PROPERTY');
			$result->addError(new Main\Error($errorMessage));
		}

		return $result;
	}

	public function addProduct($productId, $count = 1, array $data = null)
	{
		$basket = $this->getBasket();
		$basketFields = $this->getProductBasketFields($productId, $count, $data);

		$result = $this->createBasketItem($basket, $basketFields);
		$addData = $result->getData();

		if (isset($addData['BASKET_ITEM']))
		{
			/** @var Sale\BasketItemBase $basketItem */
			$basketItem = $addData['BASKET_ITEM'];
			$basketCode = $basketItem->getBasketCode();
			$addData['BASKET_CODE'] = $basketCode;

			$result->setData($addData);

			if (!empty($data))
			{
				$basketHandler = $this->getBasketDataPreserver();
				$basketHandler->preserve($basketCode, $data);
			}
		}

		return $result;
	}

	protected function getProductBasketFields($productId, $count = 1, array $data = null)
	{
		$result = [
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $count,
			'CURRENCY' => $this->internalOrder->getCurrency(), // required for bitrix17
			'MODULE' => 'catalog',
			'PRODUCT_PROVIDER_CLASS' => $this->getProductDefaultProvider(),
		];

		if ($data !== null)
		{
			$result = $data + $result; // user data priority
		}

		return $result;
	}

	protected function getProductDefaultProvider()
	{
		if (method_exists(Catalog\Product\Basket::class, 'getDefaultProviderName'))
		{
			$result = Catalog\Product\Basket::getDefaultProviderName();
		}
		else
		{
			$result = 'CCatalogProductProvider';
		}

		return $result;
	}

	protected function createBasketItem(Sale\BasketBase $basket, $basketFields)
	{
		/** @var Sale\BasketItemBase $basketItem */
		$result = new Main\Result();
		$basketItem = $basket->createItem($basketFields['MODULE'], $basketFields['PRODUCT_ID']);
		$settableFieldsMap = array_flip($basketItem::getSettableFields());
		$alreadySetFields = [
			'MODULE' => true,
			'PRODUCT_ID' => true,
		];

		// apply limits

		if (isset($basketFields['AVAILABLE_QUANTITY']))
		{
			$siblingsQuantity = $this->getBasketProductSiblingsFilledQuantity($basketItem);

			if ($siblingsQuantity > 0)
			{
				$basketFields['AVAILABLE_QUANTITY'] -= $siblingsQuantity;
			}

			if ($basketFields['AVAILABLE_QUANTITY'] < $basketFields['QUANTITY'])
			{
				$basketFields['QUANTITY'] = $basketFields['AVAILABLE_QUANTITY'];
			}
		}

		// properties

		$propertyCollection = $basketItem->getPropertyCollection();

		if (!empty($basketFields['PROPS']) && $propertyCollection)
		{
			$propertyCollection->setProperty($basketFields['PROPS']);
		}

		$alreadySetFields += [
			'PROPS' => true,
		];

		// set presets

		$presetsFields = [
			'PRODUCT_PROVIDER_CLASS' => true,
			'CALLBACK_FUNC' => true,
			'PAY_CALLBACK_FUNC' => true,
			'SUBSCRIBE' => true,
		];
		$presets = array_intersect_key($basketFields, $presetsFields);
		$presets = array_intersect_key($presets, $settableFieldsMap);

		$basketItem->setFields($presets);
		$alreadySetFields += $presetsFields;

		// get provider data

		$providerResult = $this->getBasketItemProviderData($basket, $basketItem, $basketFields);

		if ($result->isSuccess())
		{
			$providerData = (array)$providerResult->getData();

			if (
				isset($providerData['AVAILABLE_QUANTITY'], $basketFields['AVAILABLE_QUANTITY'])
				&& $providerData['AVAILABLE_QUANTITY'] < $basketFields['AVAILABLE_QUANTITY']
			)
			{
				$basketFields['AVAILABLE_QUANTITY'] = $providerData['AVAILABLE_QUANTITY'];
			}

			$basketFields += $providerData;
		}
		else
		{
			$result->addErrors($providerResult->getErrors());
		}

		// set name

		if (isset($basketFields['NAME']))
		{
			$basketItem->setField('NAME', $basketFields['NAME']);
			$alreadySetFields += [
				'NAME' => true,
			];
		}

		// set quantity

		$setQuantityResult = $basketItem->setField('QUANTITY', $basketFields['QUANTITY']);

		if (!$setQuantityResult->isSuccess())
		{
			$this->fillBasketItemAvailableQuantity($basketItem, $basketFields);

			$result->addErrors($setQuantityResult->getErrors());
		}

		$alreadySetFields += [
			'QUANTITY' => true,
		];

		// set left fields

		$leftFields = array_diff_key($basketFields, $alreadySetFields);
		$leftFields = array_intersect_key($leftFields, $settableFieldsMap);

		$setLeftFields = $basketItem->setFields($leftFields);

		if (!$setLeftFields->isSuccess())
		{
			$result->addErrors($setLeftFields->getErrors());
		}

		$result->setData([
			'BASKET_ITEM' => $basketItem,
		]);

		return $result;
	}

	protected function getBasketItemProviderData(Sale\BasketBase $basket, Sale\BasketItemBase $basketItem, $basketFields)
	{
		$result = new Main\Result();
		$initialQuantity = $basketItem->getField('QUANTITY');

		$basketItem->setFieldNoDemand('QUANTITY', $basketFields['QUANTITY']); // required for get available quantity

		$providerData = Sale\Provider::getProductData($basket, ['PRICE', 'AVAILABLE_QUANTITY'], $basketItem);
		$basketCode = $basketItem->getBasketCode();

		if (empty($providerData[$basketCode]))
		{
			$errorMessage = static::getLang('TRADING_ENTITY_SALE_ORDER_PRODUCT_NO_PROVIDER_DATA');
			$result->addError(new Main\Error($errorMessage));
		}
		else
		{
			// -- cache provider data to discount

			$discount = $basket->getOrder()->getDiscount();

			if ($discount instanceof Sale\Discount)
			{
				$basketFieldsDiscountData = array_intersect_key($basketFields, [
					'BASE_PRICE' => true,
					'DISCOUNT_PRICE' => true,
					'PRICE' => true,
					'CURRENCY' => true,
					'DISCOUNT_LIST' => true,
				]);
				$discountBasketData = $basketFieldsDiscountData + $providerData[$basketCode];

				$discount->setBasketItemData($basketCode, $discountBasketData);
			}

			// -- export data

			$result->setData($providerData[$basketCode]);
		}

		$basketItem->setFieldNoDemand('QUANTITY', $initialQuantity); // reset initial quantity

		return $result;
	}

	protected function fillBasketItemAvailableQuantity(Sale\BasketItemBase $basketItem, $fields)
	{
		$currentQuantity = (float)$basketItem->getQuantity();
		$requestedQuantity = (float)$fields['QUANTITY'];

		if ($currentQuantity < $requestedQuantity && isset($fields['AVAILABLE_QUANTITY']))
		{
			$siblingsQuantity = $this->getBasketProductSiblingsFilledQuantity($basketItem);
			$availableQuantity = (float)$fields['AVAILABLE_QUANTITY'] - $siblingsQuantity;

			if ($availableQuantity > $currentQuantity && $availableQuantity < $requestedQuantity)
			{
				$basketItem->setField('QUANTITY', $availableQuantity);
			}
		}
	}

	protected function getBasketProductSiblingsFilledQuantity(Sale\BasketItemBase $basketItem)
	{
		$result = 0;
		$searchProductId = (string)$basketItem->getProductId();

		if ($searchProductId !== '')
		{
			$basket = $basketItem->getCollection();

			/** @var Sale\BasketItemBase $basketItem*/
			foreach ($basket as $siblingItem)
			{
				if (
					$siblingItem !== $basketItem
					&& (string)$siblingItem->getProductId() === $searchProductId
					&& $siblingItem->canBuy()
				)
				{
					$result += $siblingItem->getQuantity();
				}
			}
		}

		return $result;
	}

	public function getBasketItemCode($productId)
	{
		$basket = $this->getBasket();
		$result = null;

		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if ((string)$basketItem->getProductId() === (string)$productId)
			{
				$result = $basketItem->getBasketCode();
				break;
			}
		}

		return $result;
	}

	public function getBasketItemData($basketCode)
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = static::getLang('ENTITY_ORDER_BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else if ((float)$basketItem->getQuantity() <= 0)
		{
			$errorMessage = static::getLang('ENTITY_ORDER_BASKET_ITEM_EMPTY_QUANTITY');
			$result->addError(new Main\Error($errorMessage));
		}
		else if ((float)$basketItem->getPrice() <= 0)
		{
			$errorMessage = static::getLang('ENTITY_ORDER_BASKET_ITEM_EMPTY_PRICE');
			$result->addError(new Main\Error($errorMessage));
		}
		else
		{
			$result->setData([
				'NAME' => $basketItem->getField('NAME'),
				'PRICE' => $basketItem->getPrice(),
				'QUANTITY' => $basketItem->canBuy() ? $basketItem->getQuantity() : 0,
				'MEASURE_NAME' => $basketItem->getField('MEASURE_NAME'),
				'DETAIL_PAGE_URL' => $basketItem->getField('DETAIL_PAGE_URL'),
				'VAT_RATE' => $basketItem->getVatRate() * 100,
			]);
		}

		return $result;
	}

	public function setBasketItemPrice($basketCode, $price)
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = static::getLang('TRADING_ENTITY_SALE_BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else if (Market\Data\Price::round($price) !== Market\Data\Price::round($basketItem->getPrice()))
		{
			$setResult = $basketItem->setFields([
				'CUSTOM_PRICE' => 'Y',
				'PRICE' => $price
			]);

			if (!$setResult->isSuccess())
			{
				$result->addErrors($setResult->getErrors());
			}
		}

		return $result;
	}

	public function setBasketItemQuantity($basketCode, $quantity, $silent = false)
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = static::getLang('TRADING_ENTITY_SALE_BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else if (Market\Data\Quantity::round($quantity) === Market\Data\Quantity::round($basketItem->getQuantity()))
		{
			// nothing
		}
		else if ($silent)
		{
			$basketItem->setFieldNoDemand('QUANTITY', $quantity);

			if ($this->supportsShipmentItemOverheadQuantity())
			{
				$this->syncShipmentItemQuantity($basketItem, true);
			}
		}
		else
		{
			$setResult = $basketItem->setField('QUANTITY', $quantity);

			if ($setResult->isSuccess())
			{
				$this->syncShipmentItemQuantity($basketItem);
			}
			else
			{
				$result->addErrors($setResult->getErrors());
			}
		}

		return $result;
	}

	public function getBasketPrice()
	{
		return $this->getBasket()->getPrice();
	}

	public function createShipment($deliveryId, $price = null, array $data = null)
	{
		$shipmentCollection = $this->internalOrder->getShipmentCollection();

		$this->clearOrderShipment($shipmentCollection);
		$shipment = $this->buildOrderShipment($shipmentCollection, $deliveryId, $data);

		$this->fillShipmentBasket($shipment);
		$this->fillShipmentPrice($shipment, $price);

		return new Main\Result();
	}

	protected function clearOrderShipment(Sale\ShipmentCollection $shipmentCollection)
	{
		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$shipment->delete();
			}
		}
	}

	protected function buildOrderShipment(Sale\ShipmentCollection $shipmentCollection, $deliveryId, array $data = null)
	{
		$shipment = $shipmentCollection->createItem();

		if ((int)$deliveryId > 0)
		{
			$delivery = Sale\Delivery\Services\Manager::getObjectById($deliveryId);

			if ($delivery !== null)
			{
				$deliveryName = $delivery->getNameWithParent();
			}
			else
			{
				$deliveryName = 'Not found (' . $deliveryId . ')';
			}

			$shipment->setField('DELIVERY_ID', $deliveryId);
			$shipment->setField('DELIVERY_NAME', $deliveryName);
		}

		if (!empty($data))
		{
			$settableFields = array_flip($shipment::getAvailableFields());
			$settableData = array_intersect_key($data, $settableFields);

			$shipment->setFields($settableData);
		}

		return $shipment;
	}

	protected function fillShipmentPrice(Sale\Shipment $shipment, $price = null)
	{
		if ($price !== null)
		{
			$shipment->setFields([
				'CUSTOM_PRICE_DELIVERY' => 'Y',
				'BASE_PRICE_DELIVERY' => $price,
			]);
		}
	}

	protected function fillShipmentBasket(Sale\Shipment $shipment)
	{
		/** @var Sale\BasketItem $basketItem */
		/** @var Sale\ShipmentItem $shipmentItem */
		$basket = $this->getBasket();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		foreach ($basket as $basketItem)
		{
			$shipmentItem = $shipmentItemCollection->createItem($basketItem);

			if ($shipmentItem)
			{
				$shipmentItem->setQuantity($basketItem->getQuantity());
			}
		}
	}

	protected function supportsShipmentItemOverheadQuantity()
	{
		$saleVersion = Main\ModuleManager::getVersion('sale');

		return $saleVersion !== false && CheckVersion($saleVersion, '17.0.0');
	}

	protected function syncShipmentItemQuantity(Sale\BasketItemBase $basketItem, $silent = false)
	{
		$shipment = $this->getNotSystemShipment();

		if ($shipment === null) { return; }

		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$shipmentItem = $shipmentItemCollection->createItem($basketItem);

		if ($shipmentItem === null) { return; }

		if ($silent)
		{
			$shipmentItem->setFieldNoDemand('QUANTITY', $basketItem->getQuantity());
		}
		else
		{
			$shipmentItem->setField('QUANTITY', $basketItem->getQuantity());
		}
	}

	protected function getNotSystemShipment()
	{
		$shipmentCollection = $this->internalOrder->getShipmentCollection();
		$result = null;

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$result = $shipment;
				break;
			}
		}

		return $result;
	}

	public function createPayment($paySystemId, $price = null, array $data = null)
	{
		$paymentCollection = $this->internalOrder->getPaymentCollection();

		$this->clearOrderPayment($paymentCollection);
		$payment = $this->buildOrderPayment($paymentCollection, $paySystemId, $data);
		$this->fillPaymentPrice($payment, $price);

		return new Main\Result();
	}

	protected function clearOrderPayment(Sale\PaymentCollection $paymentCollection)
	{
		/** @var Sale\Payment $payment*/
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isPaid())
			{
				$payment->setPaid(false);
			}

			$payment->delete();
		}
	}

	protected function buildOrderPayment(Sale\PaymentCollection $paymentCollection, $paySystemId, array $data = null)
	{
		$payment = $paymentCollection->createItem();

		if ((int)$paySystemId > 0)
		{
			$paySystem = Sale\PaySystem\Manager::getById($paySystemId);

			if ($paySystem !== false)
			{
				$paySystemName = $paySystem['NAME'];
			}
			else
			{
				$paySystemName = 'Not found (' . $paySystemId . ')';
			}

			$payment->setField('PAY_SYSTEM_ID', $paySystemId);
			$payment->setField('PAY_SYSTEM_NAME', $paySystemName);
		}

		if (!empty($data))
		{
			$settableFields = array_flip($payment::getAvailableFields());
			$settableData = array_intersect_key($data, $settableFields);

			$payment->setFields($settableData);
		}

		return $payment;
	}

	protected function fillPaymentPrice(Sale\Payment $payment, $price = null)
	{
		if ($price === null)
		{
			$price = $this->internalOrder->getPrice();
		}

		$payment->setField('SUM', $price);
	}

	public function setNotes($notes)
	{
		return $this->internalOrder->setField('USER_DESCRIPTION', $notes);
	}

	public function finalize()
	{
		$basket = $this->getBasket();
		$basketHandler = $this->getBasketDataPreserver();
		$basketHandler->install();

		$result = $basket->refreshData();

		$basketHandler->release();

		if ($this->isStartField && $result->isSuccess())
		{
			$hasMeaningfulFields = $this->internalOrder->hasMeaningfulField();
			$finalActionResult = $this->internalOrder->doFinalAction($hasMeaningfulFields);

			if (!$finalActionResult->isSuccess())
			{
				$result->addErrors($finalActionResult->getErrors());
			}
		}

		return $result;
	}

	public function isExistMarker($code, $condition = null)
	{
		$marker = $this->environment->getMarker();

		if ($this->internalOrder->getField('MARKED') !== 'Y')
		{
			$result = false;
		}
		else if ($marker->hasExternalEntity())
		{
			$result = ($marker->getMarkerId($this->getId(), $code, $condition) !== null);
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	public function addMarker($message, $code)
	{
		$message = $this->truncateMarkerText($message);
		$reason = $message;
		$marker = $this->environment->getMarker();

		if ($marker->hasExternalEntity())
		{
			$marker->addMarker($this->internalOrder, $this->internalOrder, $message, $code);
		}
		else
		{
			$previousReason = (string)$this->internalOrder->getField('REASON_MARKED');

			if ($previousReason !== '')
			{
				$reason = $previousReason . '<br>' . $reason;
				$reason = $this->truncateMarkerText($reason);
			}
		}

		return $this->internalOrder->setFields([
			'MARKED' => 'Y',
			'REASON_MARKED' => $reason,
		]);
	}

	protected function truncateMarkerText($text)
	{
		$result = $text;
		$maxLength = 250;

		if (strlen($result) > $maxLength)
		{
			$dots = '...';
			$dotsLength = strlen($dots);
			$result = substr($result, 0, $maxLength - $dotsLength) . $dots;
		}

		return $result;
	}

	public function removeMarker($code)
	{
		$result = new Main\Result();
		$marker = $this->environment->getMarker();
		$hasLeftMarkers = false;

		if ($marker->hasExternalEntity())
		{
			$markerId = $marker->getMarkerId($this->getId(), $code);

			if ($markerId !== null)
			{
				$deleteResult = $marker->delete($markerId);

				if ($deleteResult->isSuccess())
				{
					$hasLeftMarkers = $marker->hasMarkers($this->getId());
				}
				else
				{
					$result->addErrors($deleteResult->getErrors());
				}
			}
			else
			{
				$hasLeftMarkers = $marker->hasMarkers($this->getId());
			}
		}

		if (!$hasLeftMarkers)
		{
			$this->internalOrder->setFields([
				'MARKED' => 'N',
				'REASON_MARKED' => '',
			]);
		}

		return $result;
	}

	public function setStatus($status, $reason = null)
	{
		switch ($status)
		{
			case Status::STATUS_CANCELED:
				$result = $this->cancelOrder($reason);
			break;

			case Status::STATUS_PAYED:
				$result = $this->setPaid(true);
			break;

			case Status::STATUS_ALLOW_DELIVERY:
				$result = $this->allowDelivery();
			break;

			case Status::STATUS_DEDUCTED:
				$result = $this->deduct();
			break;

			default:
				$result = $this->internalOrder->setField('STATUS_ID', $status);
			break;
		}

		return $result;
	}

	protected function allowDelivery()
	{
		$shipmentCollection = $this->internalOrder->getShipmentCollection();
		$result = new Main\Result();

		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem() || $shipment->isAllowDelivery()) { continue; }

			$allowResult = $shipment->allowDelivery();

			if (!$allowResult->isSuccess())
			{
				$result->addErrors($allowResult->getErrors());
			}
		}

		return $result;
	}

	protected function deduct()
	{
		$shipmentCollection = $this->internalOrder->getShipmentCollection();
		$result = new Main\Result();

		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem() || $shipment->isShipped()) { continue; }

			$deductResult = $shipment->setField('DEDUCTED', 'Y');

			if (!$deductResult->isSuccess())
			{
				$result->addErrors($deductResult->getErrors());
			}
		}

		return $result;
	}

	protected function cancelOrder($reason = null)
	{
		$result = new Main\Result();

		if (!$this->internalOrder->isCanceled())
		{
			$this->cancelShipment($this->internalOrder);
			$this->cancelPayment($this->internalOrder);

			$setResult = $this->internalOrder->setField('CANCELED', 'Y');

			if (!$setResult->isSuccess())
			{
				$result->addErrors($setResult->getErrors());
			}
			else if ((string)$reason !== '')
			{
				$this->internalOrder->setField('REASON_CANCELED', $reason);
			}
		}

		return $result;
	}

	protected function cancelShipment(Sale\OrderBase $order)
	{
		/** @var Sale\Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			if ($shipment->isShipped())
			{
				$shipment->setField('DEDUCTED', 'N');
			}

			if ($shipment->isAllowDelivery())
			{
				$shipment->disallowDelivery();
			}
		}
	}

	protected function cancelPayment(Sale\OrderBase $order)
	{
		/** @var Sale\Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			if ($payment->isPaid())
			{
				$payment->setReturn('Y');
			}
		}
	}

	protected function setPaid($value)
	{
		$paymentCollection = $this->internalOrder->getPaymentCollection();
		$result = new Sale\Result();
		$value = (bool)$value;

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ((bool)$payment->isPaid() === $value) { continue; }

			$paymentResult = $payment->setPaid($value ? 'Y' : 'N');

			if (!$paymentResult->isSuccess())
			{
				$result->addErrors($paymentResult->getErrors());
			}
		}

		return $result;
	}

	public function add($externalId, EntityReference\Platform $platform)
	{
		$result = new Main\Result();

		$this->syncOrderPrice();
		$this->syncOrderPaymentSum();

		$orderResult = $this->internalOrder->save();

		if (!$orderResult->isSuccess())
		{
			$result->addErrors($orderResult->getErrors());
		}
		else
		{
			$orderId = $orderResult->getId();
			$tradingResult = $this->addTradingTable($orderId, $externalId, $platform);

			if (!$tradingResult->isSuccess())
			{
				$result->addErrors($tradingResult->getErrors());
			}
			else
			{
				$orderExportId = $orderId;
				$orderAccountNumber = (string)$this->internalOrder->getField('ACCOUNT_NUMBER');

				if ($orderAccountNumber !== '' && OrderRegistry::useAccountNumber())
				{
					$orderExportId = $orderAccountNumber;
				}

				$result->setData([
					'ID' => $orderExportId
				]);
			}
		}

		return $result;
	}

	protected function syncOrderPrice()
	{
		$currentPrice = $this->internalOrder->getPrice();
		$calculatedPrice = $this->getCalculatedOrderPrice();

		if (Market\Data\Price::round($currentPrice) !== Market\Data\Price::round($calculatedPrice))
		{
			$this->internalOrder->setField('PRICE', $calculatedPrice);
		}
	}

	protected function syncOrderPaymentSum()
	{
		$paymentCollection = $this->internalOrder->getPaymentCollection();

		if ($paymentCollection)
		{
			$lastPayment = null;
			$orderSum = $this->internalOrder->getPrice();
			$paymentSum = 0;

			/** @var Sale\Payment $payment*/
			foreach ($paymentCollection as $payment)
			{
				$paymentSum += $payment->getSum();

				if (!$payment->isPaid() && !$payment->isInner())
				{
					$lastPayment = $payment;
				}
			}

			if (
				$lastPayment !== null
				&& Market\Data\Price::round($orderSum) !== Market\Data\Price::round($paymentSum)
			)
			{
				$newPaymentSum = $orderSum - ($paymentSum - $lastPayment->getSum());

				$payment->setField('SUM', $newPaymentSum);
			}
		}
	}

	protected function getCalculatedOrderPrice()
	{
		$result = 0;
		$basket = $this->internalOrder->getBasket();
		$shipmentCollection = $this->internalOrder->getShipmentCollection();

		if ($basket)
		{
			$result += $basket->getPrice();
		}

		if (!$this->internalOrder->isUsedVat())
		{
			$result += (float)$this->internalOrder->getField('TAX_PRICE');
		}

		if ($shipmentCollection)
		{
			$result += $shipmentCollection->getPriceDelivery();
		}

		return $result;
	}

	public function update()
	{
		if ($this->eventProcessing === Listener::STATE_PROCESSING) // will be stored in handlers
		{
			$result = new Main\Result();
		}
		else if ($this->eventProcessing === Listener::STATE_SAVING) // on after order row saved
		{
			$result = new Main\Result();

			if ($this->internalOrder instanceof Sale\Order)
			{
				$changedValues = $this->internalOrder->getFields()->getChangedValues();
				$diffValues = array_diff($changedValues, $this->initialChangedValues);
				$saveValues = array_intersect_key(
					$diffValues,
					Sale\Internals\OrderTable::getEntity()->getFields()
				);

				if (!empty($saveValues))
				{
					$updateResult = Sale\OrderTable::update($this->internalOrder->getId(), $saveValues);

					if (!$updateResult->isSuccess())
					{
						$result->addErrors($updateResult->getErrors());
					}
				}
			}
		}
		else
		{
			$result = $this->internalOrder->save();
		}

		return $result;
	}

	protected function addTradingTable($orderId, $externalId, Market\Trading\Entity\Reference\Platform $platform)
	{
		return Sale\TradingPlatform\OrderTable::add([
			'ORDER_ID' => $orderId,
			'EXTERNAL_ORDER_ID' => $externalId,
			'TRADING_PLATFORM_ID' => $platform->getId(),
		]);
	}

	protected function getBasket()
	{
		$order = $this->internalOrder;
		$basket = $order->getBasket();

		if ($basket === null || $basket === false)
		{
			$basket = $this->createBasket($order);
			$order->setBasket($basket);
		}

		return $basket;
	}

	protected function createBasket(Sale\OrderBase $order)
	{
		$siteId = $order->getSiteId();
		$userId = $order->getUserId();
		$fUserId = null;

		if ($userId > 0)
		{
			$fUserId = Sale\Fuser::getIdByUserId($userId);
		}

		$basketClassName = OrderRegistry::getBasketClassName();
		$basket = $basketClassName::create($siteId);
		$basket->setFUserId($fUserId);

		return $basket;
	}

	protected function getBasketDataPreserver()
	{
		if ($this->basketDataPreserver === null)
		{
			$this->basketDataPreserver = new Internals\BasketDataPreserver();
		}

		return $this->basketDataPreserver;
	}
}