<?php

namespace Yandex\Market\Trading\Service\Marketplace;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;

class Status extends TradingService\Common\Status
{
	use Market\Reference\Concerns\HasLang;

	const STATUS_UNPAID = 'UNPAID';
	const STATUS_CANCELLED = 'CANCELLED';
	const STATUS_DELIVERED = 'DELIVERED';
	const STATUS_DELIVERY = 'DELIVERY';
	const STATUS_PICKUP = 'PICKUP';
	const STATUS_PROCESSING = 'PROCESSING';

	const STATE_STARTED = 'STARTED';
	const STATE_READY_TO_SHIP = 'READY_TO_SHIP';
	const STATE_SHOP_FAILED = 'SHOP_FAILED';
	const STATE_SHIPPED = 'SHIPPED';

	protected $provider;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Provider $provider)
	{
		parent::__construct($provider);
	}

	public function getTitle($status, $version = '')
	{
		$statusKey = strtoupper($status);
		$versionSuffix = ($version !== '' ? '_' . $version : '');

		return static::getLang('TRADING_SERVICE_MARKETPLACE_STATUS_' . $statusKey . $versionSuffix, null, $status);
	}

	public function getVariants()
	{
		return [
			static::STATUS_CANCELLED,
			static::STATE_STARTED,
			static::STATE_READY_TO_SHIP,
			static::STATE_SHIPPED,
			static::STATUS_DELIVERY,
			static::STATUS_PICKUP,
			static::STATUS_DELIVERED,
		];
	}

	public function getIncomingVariants()
	{
		return [
			static::VIRTUAL_CREATED,
			static::STATUS_CANCELLED,
			static::STATUS_PROCESSING,
			static::STATUS_DELIVERY,
			static::STATUS_PICKUP,
			static::STATUS_DELIVERED,
		];
	}

	public function getIncomingRequired()
	{
		return [
			static::STATUS_CANCELLED,
			static::STATUS_PROCESSING,
			static::STATUS_DELIVERED,
		];
	}

	public function getIncomingMeaningfulMap()
	{
		return [
			Market\Data\Trading\MeaningfulStatus::CREATED => static::VIRTUAL_CREATED,
			Market\Data\Trading\MeaningfulStatus::PAYED => static::STATUS_PROCESSING,
			Market\Data\Trading\MeaningfulStatus::CANCELED => static::STATUS_CANCELLED,
			Market\Data\Trading\MeaningfulStatus::FINISHED => static::STATUS_DELIVERED,
		];
	}

	public function getOutgoingVariants()
	{
		return [
			static::STATE_READY_TO_SHIP,
			static::STATE_SHOP_FAILED,
			static::STATE_SHIPPED,
		];
	}

	public function getOutgoingRequired()
	{
		return [
			static::STATE_READY_TO_SHIP,
			static::STATE_SHOP_FAILED,
			static::STATE_SHIPPED,
		];
	}

	public function getOutgoingMeaningfulMap()
	{
		return [
			Market\Data\Trading\MeaningfulStatus::ALLOW_DELIVERY => static::STATE_READY_TO_SHIP,
			Market\Data\Trading\MeaningfulStatus::CANCELED => static::STATE_SHOP_FAILED,
			Market\Data\Trading\MeaningfulStatus::DEDUCTED => static::STATE_SHIPPED,
		];
	}

	public function isCanceled($status)
	{
		return $status === static::STATUS_CANCELLED;
	}

	public function isProcessing($status)
	{
		return $status === static::STATUS_PROCESSING;
	}

	public function isConfirmed($status)
	{
		return in_array($status, [
			static::STATUS_PROCESSING,
			static::STATUS_DELIVERY,
			static::STATUS_PICKUP,
			static::STATUS_DELIVERED,
		], true);
	}

	public function isLeftProcessing($status)
	{
		return !$this->isProcessing($status) && $this->isConfirmed($status);
	}

	public function splitComplex($status)
	{
		$subStatues = $this->getSubStatuses();
		$result = null;

		if (in_array($status, $subStatues, true))
		{
			$result = [
				'status' => static::STATUS_PROCESSING,
				'substatus' => $status,
			];
		}

		return $result;
	}

	protected function getSubStatuses()
	{
		return [
			static::STATE_STARTED,
			static::STATE_READY_TO_SHIP,
			static::STATE_SHIPPED,
			static::STATE_SHOP_FAILED,
		];
	}
}