<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Sale;

class Profile extends Market\Trading\Entity\Reference\Profile
{
	use Market\Reference\Concerns\HasLang;

	/** @var Environment*/
	protected $environment;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getEnum($userId, $personTypeId)
	{
		$result = [];

		$query = Sale\Internals\UserPropsTable::getList([
			'filter' => [
				'=USER_ID' => $userId,
				'=PERSON_TYPE_ID' => (int)$personTypeId,
			],
			'select' => [ 'ID', 'NAME' ],
			'order' => [ 'DATE_UPDATE' => 'DESC', 'ID' => 'DESC' ]
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => $row['NAME']
			];
		}

		return $result;
	}

	public function getValues($profileId)
	{
		if (method_exists('\Bitrix\Sale\OrderUserProperties', 'getProfileValues'))
		{
			$result = Sale\OrderUserProperties::getProfileValues($profileId);
		}
		else
		{
			$result = $this->loadProfileValues($profileId);
		}

		return $result;
	}

	public function createProfile($userId, $personTypeId, array $values = [])
	{
		$values += $this->getDefaultValues();
		$profileName = isset($values['NAME']) ? $values['NAME'] : static::getLang('TRADING_ENTITY_SALE_PROFILE_VALUE_NAME');
		$profileId = $this->addProfile($userId, $personTypeId, $profileName);
		$profileValues = $this->convertPropertyValues($personTypeId, $values);

		if (!empty($profileValues))
		{
			$this->saveProfileValues($userId, $personTypeId, $profileId, $profileValues);
		}

		return $profileId;
	}

	public function update($profileId, array $values)
	{
		$profile = $this->fetchProfile($profileId);
		$profileValues = $this->convertPropertyValues($profile['PERSON_TYPE_ID'], $values);
		$updateResults = [];

		if (isset($values['NAME']))
		{
			$updateResults[] = $this->updateProfile($profileId, $values['NAME']);
		}

		if (!empty($profileValues))
		{
			$updateResults[] = $this->saveProfileValues($profile['USER_ID'], $profile['PERSON_TYPE_ID'], $profileId, $profileValues);
		}

		return !empty($updateResults)
			? Market\Result\Facade::merge($updateResults)
			: new Main\Entity\UpdateResult();
	}

	public function getEditUrl($profileId)
	{
		return Market\Ui\Admin\Path::getPageUrl('sale_buyers_profile_edit', [
			'id' => (int)$profileId,
			'lang' => LANGUAGE_ID,
		]);
	}

	protected function fetchProfile($profileId)
	{
		$query = Sale\Internals\UserPropsTable::getById($profileId);
		$result = $query->fetch();

		if ($result === false)
		{
			$errorMessage = static::getLang('TRADING_ENTITY_SALE_PROFILE_NOT_FOUND', [
				'#ID#' => $profileId,
			]);

			throw new Main\ObjectNotFoundException($errorMessage);
		}

		return $result;
	}

	protected function addProfile($userId, $personTypeId, $name)
	{
		$addResult = Sale\Internals\UserPropsTable::add([
			'NAME' => $name,
			'USER_ID' => $userId,
			'PERSON_TYPE_ID' => $personTypeId
		]);

		if (!$addResult->isSuccess())
		{
			$resultMessage = implode(PHP_EOL, $addResult->getErrorMessages());
			$errorMessage = static::getLang('TRADING_ENTITY_SALE_PROFILE_CANT_ADD_PROFILE', [
				'#MESSAGE#' => $resultMessage,
			]);

			throw new Main\SystemException($errorMessage);
		}

		return $addResult->getId();
	}

	protected function updateProfile($profileId, $name)
	{
		return Sale\Internals\UserPropsTable::update($profileId, [
			'NAME' => $name,
		]);
	}

	protected function saveProfileValues($userId, $personTypeId, $profileId, $values)
	{
		$result = new Main\Entity\UpdateResult();
		$errors = [];

		\CSaleOrderUserProps::DoSaveUserProfile($userId, $profileId, '', $personTypeId, $values, $errors);

		foreach ($errors as $error)
		{
			$result->addError(new Main\Error($error['TEXT'], $error['CODE']));
		}

		return $result;
	}

	protected function loadProfileValues($profileId)
	{
		$result = [];

		$dbUserPropsValues = \CSaleOrderUserPropsValue::GetList(
			[],
			[ 'USER_PROPS_ID' => (int)$profileId, ],
			false,
			false,
			[ 'VALUE', 'PROP_TYPE', 'ORDER_PROPS_ID' ]
		);

		while ($propValue = $dbUserPropsValues->Fetch())
		{
			if ($propValue['PROP_TYPE'] === 'ENUM')
			{
				$propValue['VALUE'] = explode(',', $propValue['VALUE']);
			}

			if ($propValue['PROP_TYPE'] === 'LOCATION' && !empty($propValue['VALUE']))
			{
				$propValue['VALUE'] = \CSaleLocation::getLocationCODEbyID($propValue['VALUE']);
			}

			$result[$propValue['ORDER_PROPS_ID']] = $propValue['VALUE'];
		}

		return $result;
	}

	protected function getDefaultValues()
	{
		$result = [];
		$fields = [
			'NAME',
			'COMPANY',
			'LOCATION',
			'ADDRESS',
			'ZIP',
			'PHONE',
			'EMAIL',
		];

		foreach ($fields as $fieldName)
		{
			if ($fieldName === 'LOCATION')
			{
				$geoId = $this->getPropertyDefaultValue('GEO_ID');
				$cityName = $this->getPropertyDefaultValue('CITY');

				$value = $this->getLocationValueByName($cityName, $geoId);
			}
			else
			{
				$value = $this->getPropertyDefaultValue($fieldName);
			}

			if (!Market\Utils\Value::isEmpty($value))
			{
				$result[$fieldName] = $value;
			}
		}

		return $result;
	}

	protected function convertPropertyValues($personTypeId, array $values)
	{
		$propertyType = $this->environment->getProperty();

		return $propertyType->convertMeaningfulValues($personTypeId, $values);
	}

	protected function getLocationValueByName($name, $geoId)
	{
		$result = null;
		$locationType = $this->environment->getLocation();
		$locationId = $locationType->getLocation([
			'id' => $geoId,
			'name' => $name,
		]);

		if ($locationId !== null)
		{
			$result = \CSaleLocation::getLocationCODEbyID($locationId);
		}

		return $result;
	}

	protected function getPropertyDefaultValue($propertyType, $defaultValue = '')
	{
		return (string)static::getLang(
			'TRADING_ENTITY_SALE_PROFILE_VALUE_' . $propertyType,
			null,
			(string)$defaultValue
		);
	}
}