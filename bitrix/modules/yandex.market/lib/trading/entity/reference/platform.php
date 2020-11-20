<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;

abstract class Platform
{
	protected $environment;
	protected $serviceCode;
	protected $siteId;

	public function __construct(Environment $environment, $serviceCode, $siteId)
	{
		$this->environment = $environment;
		$this->serviceCode = $serviceCode;
		$this->siteId = $siteId;
	}

	/**
	 * @return int|string|null
	 */
	public function getId()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getId');
	}

	/**
	 * @return bool
	 */
	public function isInstalled()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'isInstalled');
	}

	/**
	 * @param TradingService\Reference\Info $info
	 *
	 * @return int
	 * @throws Main\SystemException
	 */
	public function install(TradingService\Reference\Info $info)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'install');
	}

	/**
	 * @return Main\Result
	 */
	public function uninstall()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'uninstall');
	}

	/**
	 * @param string $newCode
	 *
	 * @return Main\Result
	 */
	public function migrate($newCode)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'migrate');
	}

	/**
	 * @param TradingService\Reference\Info $info
	 *
	 * @return Main\Result
	 * @throws Main\SystemException
	 */
	public function update(TradingService\Reference\Info $info)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'update');
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'isActive');
	}

	/**
	 * @return Main\Result
	 */
	public function activate()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'activate');
	}

	/**
	 * @return Main\Result
	 */
	public function deactivate()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'deactivate');
	}

	/**
	 * @param string|int $orderId
	 *
	 * @return string
	 */
	public function getOrderXmlId($orderId)
	{
		if ($orderId === null)
		{
			$orderId = 'CART';
		}

		return 'YAMARKET_' . $this->getId() . '_' . $orderId;
	}
}