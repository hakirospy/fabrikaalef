<?php

namespace Yandex\Market\Trading\Service\Marketplace\Api\SendBoxes;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Response extends Market\Api\Reference\Response
{
	const STATUS_OK = 'OK';
	const STATUS_ERROR = 'ERROR';

	public function validate()
	{
		$result = parent::validate();

		if (!$result->isSuccess())
		{
			// nothing
		}
		else if ($statusError = $this->validateStatus())
		{
			$result->addError($statusError);
		}
		else if ($resultError = $this->validateResult())
		{
			$result->addError($resultError);
		}

		return $result;
	}

	protected function validateStatus()
	{
		$status = (string)$this->getField('status');
		$result = null;

		if ($status === '')
		{
			$message = Market\Config::getLang('API_ORDER_BOXES_RESPONSE_STATUS_NOT_SET');
			$result = new Main\Error($message);
		}
		else if ($status === static::STATUS_ERROR)
		{
			$errors = $this->getField('errors');
			$messageList = [];

			if (is_array($errors))
			{
				foreach ($errors as $error)
				{
					if (isset($error['message']))
					{
						$messageList[] = $error['message'] . (isset($error['code']) ? ' [' . $error['code'] . ']' : '');
					}
				}
			}

			if (empty($messageList))
			{
				$messageList[] = Market\Config::getLang('API_ORDER_BOXES_RESPONSE_STATUS_ERROR');
			}

			$message = implode(PHP_EOL, $messageList);
			$result = new Main\Error($message);
		}
		else if ($status !== static::STATUS_OK)
		{
			$message = Market\Config::getLang('API_ORDER_BOXES_RESPONSE_STATUS_UNKNOWN', [ '#STATUS#' => $status ]);
			$result = new Main\Error($message);
		}

		return $result;
	}

	protected function validateResult()
	{
		$responseResult = $this->getField('result');
		$result = null;

		if (!is_array($responseResult))
		{
			$message = Market\Config::getLang('API_ORDER_BOXES_RESPONSE_RESULT_NOT_SET');
			$result = new Main\Error($message);
		}
		else if (!isset($responseResult['boxes']))
		{
			$message = Market\Config::getLang('API_ORDER_BOXES_RESPONSE_RESULT_BOXES_NOT_SET');
			$result = new Main\Error($message);
		}

		return $result;
	}
}