<?php

namespace Yandex\Market\Api\Reference;

use Bitrix\Main;
use Yandex\Market;

class ResponseWithResult extends Response
{
	const STATUS_OK = 'OK';
	const STATUS_ERROR = 'ERROR';

	public function validate()
	{
		$result = new Main\Result();

		if ($this->getStatus() !== static::STATUS_OK)
		{
			$responseErrors = $this->getResponseErrors();

			$result->addErrors($responseErrors);
		}

		return $result;
	}

	public function getStatus()
	{
		return (string)$this->getField('status');
	}

	protected function getResponseErrors()
	{
		$result = [];
		$errors = (array)$this->getField('errors');

		foreach ($errors as $error)
		{
			$result[] = new Main\Error($error['message'], $error['code']);
		}

		return $result;
	}
}