<?php

namespace Yandex\Market\Reference\Storage;

use Yandex\Market;
use Bitrix\Main;

abstract class Collection extends Market\Reference\Common\Collection
{
	/** @var Model[] */
	protected $collection = [];
	/** @var Model */
	protected $parent;

	public static function getClassName()
	{
		return '\\' . get_called_class();
	}

	/**
	 * Загружаем коллекцию для родительской сущности
	 *
	 * @param Model $parent
	 * @param array $filter
	 *
	 * @return static
	 * @throws Main\SystemException
	 */
	public static function load(Model $parent, $filter)
	{
		if ($parent->getId() > 0)
		{
			$collection = static::loadByFilter($filter);
			$collection->setParent($parent);
		}
		else
		{
			$collection = new static();
			$collection->setParent($parent);
		}

		return $collection;
	}

	/**
	 * Загружаем коллекцию по фильтру
	 *
	 * @param array $filter
	 *
	 * @return static
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function loadByFilter($filter)
	{
		$modelClassName = static::getItemReference();
		$collection = new static();

		if (!isset($modelClassName)) { throw new Main\SystemException('reference item not defined'); }

		$modelList = $modelClassName::loadList($filter);
		/** @var Model $model */
		foreach ($modelList as $model)
		{
			$model->setCollection($collection);
			$collection->addItem($model);
		}

		return $collection;
	}
}