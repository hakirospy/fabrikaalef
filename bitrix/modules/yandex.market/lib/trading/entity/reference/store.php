<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

abstract class Store
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * ����� �������
	 *
	 * @param string|null $siteId
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getEnum($siteId = null)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getEnum');
	}

	/**
	 * ������ �� ���������
	 *
	 * @return string[]
	 */
	public function getDefaults()
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getDefaults');
	}

	/**
	 * ������� ������ �� ��������� �������
	 *
	 * @param string[] $stores
	 * @param int[] $productIds
	 *
	 * @return array{
	 *  ID: int,
	 *  TIMESTAMP_X: Main\Type\DateTime,
	 *  QUANTITY: float|null,
	 *  QUANTITY_LIST: array<string, float>|null
	 * }[]
	 */
	public function getAmounts($stores, $productIds)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getAmounts');
	}

	/**
	 * ������ �� ������� ��� ������������ �������
	 *
	 * @param int[] $productIds
	 * @param array<int, float[]>|null $quantities
	 * @param array $context
	 *
	 * @return array<int|string, array>
	 */
	public function getBasketData($productIds, $quantities = null, array $context = [])
	{
		return [];
	}
}