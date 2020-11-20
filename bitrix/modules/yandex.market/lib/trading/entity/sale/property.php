<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Sale;

class Property extends Market\Trading\Entity\Reference\Property
{
	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getEnum($personTypeId)
	{
		$result = [];
		$personTypeId = (int)$personTypeId;

		if ($personTypeId > 0)
		{
			$query = Sale\Internals\OrderPropsTable::getList([
				'filter' => [
					'=PERSON_TYPE_ID' => $personTypeId,
					'=ACTIVE' => 'Y',
				],
				'order' => [
					'SORT' => 'asc',
					'ID' => 'asc',
				],
			]);

			while ($propertyRow = $query->fetch())
			{
				$result[] = [
					'ID' => $propertyRow['ID'],
					'VALUE' => $propertyRow['NAME'],
					'TYPE' => $this->getPropertyType($propertyRow),
				];
			}
		}

		return $result;
	}

	public function convertMeaningfulValues($personTypeId, array $values)
	{
		$enum = $this->getEnum($personTypeId);
		$result = [];

		foreach ($enum as $option)
		{
			if (isset($option['TYPE'], $values[$option['TYPE']]))
			{
				$result[$option['ID']] = $values[$option['TYPE']];
			}
		}

		return $result;
	}

	public function formatMeaningfulValues($personTypeId, array $values)
	{
		if (isset($values['PHONE']))
		{
			$values['PHONE'] = Market\Data\Phone::format($values['PHONE']);
		}

		return $values;
	}

	protected function getPropertyType($propertyRow)
	{
		$propertyCode = strtoupper($propertyRow['CODE']);
		$propertyType = null;

		if ($propertyRow['IS_EMAIL'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['EMAIL']))
		{
			$propertyType = 'EMAIL';
		}
		else if ($propertyRow['IS_PHONE'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['PHONE', 'TEL']))
		{
			$propertyType = 'PHONE';
		}
		else if ($propertyRow['IS_LOCATION'] === 'Y')
		{
			$propertyType = 'LOCATION';
		}
		else if ($propertyRow['IS_ADDRESS'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['ADDRESS', 'COMPANY_ADR', 'COMPANY_ADDRESS']))
		{
			$propertyType = 'ADDRESS';
		}
		else if ($propertyRow['IS_ZIP'] === 'Y' || $propertyCode === 'ZIP' || $propertyCode === 'INDEX')
		{
			$propertyType = 'ZIP';
		}
		else if ($this->isMatchPropertyCode($propertyCode, ['CITY']))
		{
			$propertyType = 'CITY';
		}
		else if ($propertyCode === 'COMPANY')
		{
			$propertyType = 'COMPANY';
		}
		else if ($propertyRow['IS_PROFILE_NAME'] === 'Y' || $propertyRow['IS_PAYER'] === 'Y')
		{
			$propertyType = 'NAME';
		}

		return $propertyType;
	}

	protected function isMatchPropertyCode($haystack, $needles)
	{
		$haystack = strtoupper($haystack);
		$result = false;

		foreach ($needles as $needle)
		{
			if (strpos($haystack, $needle) !== false)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}
}