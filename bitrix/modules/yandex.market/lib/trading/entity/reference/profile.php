<?php

namespace Yandex\Market\Trading\Entity\Reference;

use Yandex\Market;
use Bitrix\Main;

abstract class Profile
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * ����� �������� ������������
	 *
	 * @param int $userId
	 * @param int $personTypeId
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getEnum($userId, $personTypeId)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getEnum');
	}

	/**
	 * �������� �������
	 *
	 * @param int $userId
	 * @param int $personTypeId
	 * @param array $values
	 *
	 * @return int
	 * @throws Main\SystemException
	 */
	public function createProfile($userId, $personTypeId, array $values = [])
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'createProfile');
	}

	/**
	 * @param int $profileId
	 * @param array $values
	 *
	 * @return Main\Entity\UpdateResult
	 */
	public function update($profileId, array $values)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'update');
	}

	/**
	 * �������� �������
	 *
	 * @param int $profileId
	 *
	 * @return array<int, mixed>
	 */
	public function getValues($profileId)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getValues');
	}

	/**
	 * ����� �������� �������������� �������
	 *
	 * @param int $profileId
	 *
	 * @return string
	 */
	public function getEditUrl($profileId)
	{
		throw new Market\Exceptions\NotImplementedMethod(static::class, 'getEditUrl');
	}
}