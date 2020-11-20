<?php

namespace Yandex\Market\Reference\Storage;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

abstract class Model extends Market\Reference\Common\Model
{
	public static function getClassName()
	{
		return '\\' . get_called_class();
	}

	/**
	 * Загружаем список объектов по параметрам запроса d7
	 *
	 * @param array $parameters
	 *
	 * @return static[]
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function loadList($parameters = array())
	{
		$result = [];
		$tableClass = static::getDataClass();
		$distinctField = null;
		$distinctMap = null;

		if (isset($parameters['distinct']))
		{
			$distinctMap = [];

			if ($parameters['distinct'] === true)
			{
				$distinctField = $tableClass::getEntity()->getPrimary();
			}
			else
			{
				$distinctField = $parameters['distinct'];
			}

			unset($parameters['distinct']);
		}

		$query = $tableClass::getList($parameters);

		while ($itemData = $query->fetch())
		{
			if ($distinctField !== null && isset($itemData[$distinctField]))
			{
				$itemDistinctValue = $itemData[$distinctField];

				if (isset($distinctMap[$itemDistinctValue]))
				{
					continue;
				}
				else
				{
					$distinctMap[$itemDistinctValue] = true;
				}
			}

			$result[] = new static($itemData);
		}

		return $result;
	}

	/**
	 * Загружаем объект по ид
	 *
	 * @param $id int
	 *
	 * @return static
	 * @throws Main\ObjectNotFoundException
	 */
	public static function loadById($id)
	{
		$result = null;
		$tableClass = static::getDataClass();
		$query = $tableClass::getById($id);

		if ($itemData = $query->fetch())
		{
			$result = new static($itemData);
		}
		else
		{
			throw new Main\ObjectNotFoundException(Market\Config::getLang('REFERENCE_STORAGE_MODEL_LOAD_NOT_FOUND'));
		}

		return $result;
	}

	/**
	 * @return String|null
	 */
	public static function getParentReferenceField()
	{
		return null;
	}

	/**
	 * @return Table
	 */
	public static function getDataClass()
	{
		throw new Main\SystemException('not implemented');
	}

	protected function loadChildCollection($fieldKey)
	{
		$collectionClassName = $this->getChildCollectionReference($fieldKey);
		$result = null;

		if (!isset($collectionClassName)) { throw new Main\SystemException('child reference not found'); }

		if ($this->hasField($fieldKey))
		{
			$dataList = (array)$this->getField($fieldKey);
			$result = $collectionClassName::initialize($dataList, $this);
		}
		else if ($this->getId() > 0)
		{
			$queryParams = $this->getChildCollectionQueryParameters($fieldKey);

			$result = $collectionClassName::load($this, $queryParams);
		}
		else
		{
			$result = new $collectionClassName;
			$result->setParent($this);
		}

		return $result;
	}

	protected function getChildCollectionQueryParameters($fieldKey)
	{
		$tableClass = static::getDataClass();
		$reference = $tableClass::getReference($this->getId());

		if (!isset($reference[$fieldKey]['LINK'])) { throw new Main\SystemException('child reference not found'); }

		$queryParams = [
			'filter' => $tableClass::makeReferenceLinkFilter($reference[$fieldKey]['LINK'])
		];

		if (isset($reference[$fieldKey]['ORDER']))
		{
			$queryParams['order'] = $reference[$fieldKey]['ORDER'];
		}

		return $queryParams;
	}

	/**
	 * @param $fieldKey
	 *
	 * @return Collection
	 */
	protected function getChildCollectionReference($fieldKey)
	{
		return null;
	}
}