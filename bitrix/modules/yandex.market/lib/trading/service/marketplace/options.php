<?php

namespace Yandex\Market\Trading\Service\Marketplace;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;
use Yandex\Market\Trading\Entity as TradingEntity;

class Options extends TradingService\Common\Options
{
	/** @var Provider */
	protected $provider;

	protected static function includeMessages()
	{
		parent::includeMessages();
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Provider $provider)
	{
		parent::__construct($provider);
	}

	public function getTitle()
	{
		return static::getLang('TRADING_SERVICE_MARKETPLACE_TITLE');
	}

	public function getPaySystemId()
	{
		return (int)$this->getValue('PAY_SYSTEM_ID');
	}

	public function getDeliveryId()
	{
		return (int)$this->getValue('DELIVERY_ID');
	}

	public function getProductStores()
	{
		return (array)$this->getRequiredValue('PRODUCT_STORE');
	}

	public function isAllowModifyPrice()
	{
		return true;
	}

	public function getTabs()
	{
		return [
			'COMMON' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_COMMON'),
				'sort' => 1000,
			],
			'STORE' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STORE'),
				'sort' => 2000,
			],
			'STATUS' => [
				'name' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STATUS'),
				'sort' => 3000,
				'data' => [
					'WARNING' => static::getLang('TRADING_SERVICE_MARKETPLACE_TAB_STATUS_NOTE'),
				]
			],
		];
	}

	public function getFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return
			$this->getCommonFields($environment, $siteId)
			+ $this->getCompanyFields($environment, $siteId)
			+ $this->getIncomingRequestFields($environment, $siteId)
			+ $this->getOauthRequestFields($environment, $siteId)
			+ $this->getOrderPersonFields($environment, $siteId)
			+ $this->getOrderPaySystemFields($environment, $siteId)
			+ $this->getOrderDeliveryFields($environment, $siteId)
			+ $this->getOrderPropertyUtilFields($environment, $siteId)
			+ $this->getProductSkuMapFields($environment, $siteId)
			+ $this->getProductStoreFields($environment, $siteId)
			+ $this->getProductPriceFields($environment, $siteId)
			+ $this->getStatusInFields($environment, $siteId)
			+ $this->getStatusOutFields($environment, $siteId);
	}

	protected function getPersonTypeDefaultValue(TradingEntity\Reference\PersonType $personType, $siteId)
	{
		return $personType->getLegalId($siteId);
	}

	protected function getOrderPersonFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getOrderPersonFields($environment, $siteId);
		$overrides = [
			'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
			'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
			'SORT' => 3200,
		];

		return array_map(
			static function($field) use ($overrides) { return $overrides + $field; },
			$result
		);
	}

	protected function getOrderPaySystemFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$paySystem = $environment->getPaySystem();
			$paySystemEnum = $paySystem->getEnum($siteId);
			$firstPaySystem = reset($paySystemEnum);

			$result = [
				'PAY_SYSTEM_ID' => [
					'TYPE' => 'enumeration',
					'MANDATORY' => $paySystem->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_PAY_SYSTEM_ID'),
					'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $paySystemEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $firstPaySystem !== false ? $firstPaySystem['ID'] : null,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => 3300,
				]
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderDeliveryFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$delivery = $environment->getDelivery();
			$deliveryEnum = $delivery->getEnum($siteId);
			$defaultDelivery = null;
			$emptyDelivery = array_filter($deliveryEnum, function($option) {
				return $option['TYPE'] === Market\Data\Trading\Delivery::EMPTY_DELIVERY;
			});

			if (empty($emptyDelivery))
			{
				$firstEmptyDelivery = reset($emptyDelivery);
				$defaultDelivery = $firstEmptyDelivery['ID'];
			}
			else if (!empty($deliveryEnum))
			{
				$firstDelivery = reset($deliveryEnum);
				$defaultDelivery = $firstDelivery['ID'];
			}

			$result = [
				'DELIVERY_ID' => [
					'TYPE' => 'enumeration',
					'MANDATORY' => $delivery->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_MARKETPLACE_OPTION_DELIVERY_ID'),
					'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $deliveryEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $defaultDelivery,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => 3400,
				],
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderPropertyUtilFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getOrderPropertyUtilFields($environment, $siteId);
		$overrides = [
			'GROUP' => static::getLang('TRADING_SERVICE_MARKETPLACE_GROUP_ORDER'),
			'SORT' => 3500,
		];

		return array_map(
			static function($field) use ($overrides) { return $overrides + $field; },
			$result
		);
	}

	protected function getProductSkuMapFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getProductSkuMapFields($environment, $siteId);
		$overrides = [
			'HIDDEN' => 'N',
		];

		return array_map(
			static function($field) use ($overrides) { return $overrides + $field; },
			$result
		);
	}

	protected function getProductPriceFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = parent::getProductPriceFields($environment, $siteId);

		if (!Market\Config::isExpertMode())
		{
			$overrides = [
				'HIDDEN' => 'Y',
			];

			$result = array_map(
				static function($field) use ($overrides) { return $overrides + $field; },
				$result
			);
		}

		return $result;
	}
}