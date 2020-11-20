<?php

namespace Yandex\Market\Trading\Service\Turbo;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class Options extends TradingService\Common\Options
{
	const USER_RULE_MATCH_ANY = 'matchAny';
	const USER_RULE_MATCH_EMAIL = 'matchEmail';
	const USER_RULE_MATCH_PHONE = 'matchPhone';
	const USER_RULE_ANONYMOUS = 'anonymous';

	/** @var Provider */
	protected $provider;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
		parent::includeMessages();
	}

	public function __construct(Provider $provider)
	{
		parent::__construct($provider);
	}

	public function getUserRule()
	{
		return (string)$this->getValue('ORDER_USER_RULE') ?: static::USER_RULE_MATCH_ANY;
	}

	public function getDeliveryId()
	{
		return (string)$this->getValue('DELIVERY_ID');
	}

	public function getPaySystemId($paySystemType)
	{
		return $this->getValue('PAY_SYSTEM_' . strtoupper($paySystemType));
	}

	public function isAllowModifyBasket()
	{
		return true;
	}

	public function isAllowModifyPrice()
	{
		return true;
	}

	public function getTitle()
	{
		return static::getLang('TRADING_SERVICE_TURBO_TITLE');
	}

	public function getTabs()
	{
		return [
			'COMMON' => [
				'name' => static::getLang('TRADING_SERVICE_TURBO_TAB_COMMON'),
				'sort' => 1000,
			],
			'STORE' => [
				'name' => static::getLang('TRADING_SERVICE_TURBO_TAB_STORE'),
				'sort' => 2000,
			],
			'STATUS' => [
				'name' => static::getLang('TRADING_SERVICE_TURBO_TAB_STATUS'),
				'sort' => 3000,
			],
		];
	}

	public function getFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return
			$this->getIncomingRequestFields($environment, $siteId)
			+ $this->getOrderPaySystemFields($environment, $siteId)
			+ $this->getOrderDeliveryFields($environment, $siteId)
			+ $this->getOrderUserFields($environment, $siteId)
			+ $this->getOrderPersonFields($environment, $siteId)
			+ $this->getOrderPropertyFields($environment, $siteId)
			+ $this->getProductPriceFields($environment, $siteId)
			+ $this->getStatusInFields($environment, $siteId);
	}

	protected function getOrderUserFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return [
			'ORDER_USER_RULE' => [
				'TYPE' => 'enumeration',
				'NAME' => static::getLang('TRADING_SERVICE_TURBO_OPTION_ORDER_USER_RULE'),
				'GROUP' => static::getLang('TRADING_SERVICE_TURBO_GROUP_PROPERTY'),
				'VALUES' => $this->getOrderUserRuleEnum(),
				'HIDDEN' => Market\Config::isExpertMode() ? 'N' : 'Y',
				'SORT' => 3400,
				'SETTINGS' => [
					'DEFAULT_VALUE' => static::USER_RULE_MATCH_ANY,
					'ALLOW_NO_VALUE' => 'N',
					'STYLE' => 'max-width: 450px;'
				],
			],
		];
	}

	protected function getOrderUserRuleEnum()
	{
		$variants = [
			static::USER_RULE_MATCH_ANY,
			static::USER_RULE_MATCH_PHONE,
			static::USER_RULE_MATCH_EMAIL,
			static::USER_RULE_ANONYMOUS,
		];
		$result = [];

		foreach ($variants as $variant)
		{
			$variantKey = strtoupper($variant);

			$result[] = [
				'ID' => $variant,
				'VALUE' => static::getLang('TRADING_SERVICE_TURBO_OPTION_ORDER_USER_RULE_' . $variantKey, null, $variant),
			];
		}

		return $result;
	}

	protected function getOrderPaySystemFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		try
		{
			$paySystem = $environment->getPaySystem();
			$paySystemEnum = $paySystem->getEnum($siteId);
			$firstPaySystem = reset($paySystemEnum);
			$servicePaySystem = $this->provider->getPaySystem();
			$result = [];
			$sort = 3200;

			foreach ($servicePaySystem->getTypeVariants() as $servicePaySystemType)
			{
				$typeTitle = $servicePaySystem->getTypeTitle($servicePaySystemType);

				$result['PAY_SYSTEM_' . strtoupper($servicePaySystemType)] = [
					'TYPE' => 'enumeration',
					'MANDATORY' => $paySystem->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_TURBO_OPTION_PAY_SYSTEM', [
						'#TYPE#' => $typeTitle,
					]),
					'GROUP' => static::getLang('TRADING_SERVICE_TURBO_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_TURBO_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $paySystemEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $firstPaySystem !== false ? $firstPaySystem['ID'] : null,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => $sort,
				];

				++$sort;
			}
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
			$firstDelivery = reset($deliveryEnum);

			$result = [
				'DELIVERY_ID' => [
					'TYPE' => 'enumeration',
					'MANDATORY' => $delivery->isRequired() ? 'Y' : 'N',
					'NAME' => static::getLang('TRADING_SERVICE_TURBO_OPTION_DELIVERY_ID'),
					'GROUP' => static::getLang('TRADING_SERVICE_TURBO_GROUP_ORDER'),
					'GROUP_DESCRIPTION' => static::getLang('TRADING_SERVICE_TURBO_GROUP_ORDER_DESCRIPTION'),
					'VALUES' => $deliveryEnum,
					'SETTINGS' => [
						'DEFAULT_VALUE' => $firstDelivery !== false ? $firstDelivery['ID'] : null,
						'STYLE' => 'max-width: 220px;',
					],
					'SORT' => 3300,
				],
			];
		}
		catch (Market\Exceptions\NotImplemented $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getOrderPropertyFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return
			$this->getOrderPropertyUserFields($environment, $siteId)
			+ $this->getOrderPropertyUtilFields($environment, $siteId);
	}

	protected function getOrderPropertyUserFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$fields = Model\Order\User::getMeaningfulFields();
		$options = [];

		foreach ($fields as $fieldName)
		{
			$options[$fieldName] = [
				'NAME' => Model\Order\User::getMeaningfulFieldTitle($fieldName),
				'GROUP' => static::getLang('TRADING_SERVICE_TURBO_GROUP_PROPERTY'),
			];
		}

		return $this->createPropertyFields($environment, $siteId, $options, 3600);
	}
}