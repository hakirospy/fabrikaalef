<?php

namespace Yandex\Market\Migration;

use Yandex\Market;
use Bitrix\Main;

class StorageFacade
{
	public static function addNewFields(Main\DB\Connection $connection, Main\Entity\Base $entity)
	{
		$tableName = $entity->getDBTableName();
		$sqlHelper = $connection->getSqlHelper();
		$dbFields = $connection->getTableFields($tableName);

		foreach ($entity->getFields() as $field)
		{
			$fieldName = $field->getName();

			if (!isset($dbFields[$fieldName]) && $field instanceof Main\Entity\ScalarField)
			{
				$columnName = $field->getColumnName();
				$columnType = $sqlHelper->getColumnTypeByField($field);

				$sql =
					'ALTER TABLE ' . $sqlHelper->quote($tableName)
					. ' ADD COLUMN ' . $sqlHelper->quote($columnName) . ' ' . $columnType;

				$connection->queryExecute($sql);
			}
		}
	}

	public static function dropIndexes(Main\DB\Connection $connection, Main\Entity\Base $entity, $indexes)
	{
		$tableName = $entity->getDBTableName();
		$sqlHelper = $connection->getSqlHelper();

		foreach ($indexes as $index)
		{
			try
			{
				$connection->queryExecute(sprintf(
					'DROP INDEX %s ON %s',
					$sqlHelper->quote($index),
					$sqlHelper->quote($tableName)
				));
			}
			catch (Main\DB\SqlQueryException $exception)
			{
				// not exists
			}
		}
	}
}