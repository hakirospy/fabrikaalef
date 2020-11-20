<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

abstract class Property
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * ����� ������� ������
	 *
	 * @param int $personTypeId
	 *
	 * @return array{ID: string, VALUE: string, TYPE: string|null}[]
	 */
	public function getEnum($personTypeId)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getEnum');
	}

	/**
	 * �������������� ������� �������� ������� array<TYPE, VALUE> � array<PROPERTY_ID, VALUE>
	 *
	 * @param int $personTypeId
	 * @param array<string, mixed> $values
	 *
	 * @return array<string, mixed>
	 */
	public function convertMeaningfulValues($personTypeId, array $values)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'convertMeaningfulValues');
	}

	/**
	 * @param int $personTypeId
	 * @param array<string, mixed> $values
	 *
	 * @return array<string, mixed>
	 */
	public function formatMeaningfulValues($personTypeId, array $values)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'formatMeaningfulValues');
	}
}