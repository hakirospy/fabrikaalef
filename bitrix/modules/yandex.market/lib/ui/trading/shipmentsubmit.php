<?php

namespace Yandex\Market\Ui\Trading;

use Bitrix\Main;
use Yandex\Market;

class ShipmentSubmit extends Market\Ui\Reference\Page
{
	use Market\Reference\Concerns\HasLang;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	protected function getReadRights()
	{
		return Market\Ui\Access::RIGHTS_PROCESS_TRADING;
	}

	protected function getWriteRights()
	{
		return Market\Ui\Access::RIGHTS_PROCESS_TRADING;
	}

	public function hasRequest()
	{
		return $this->request->isPost();
	}

	public function processRequest()
	{
		try
		{
			$requestShipment = $this->getRequestShipment();
			$setupId = $requestShipment->getSetupId();
			$setup = Market\Trading\Setup\Model::loadById($setupId);

			if ($this->needCheckAccess())
			{
				$localOrder = $this->loadLocalOrder($setup, $requestShipment->getOrderId());
				$this->checkOrderAccess($localOrder);
			}

			$this->submitBoxes($setup, $requestShipment);
			$this->flushOrderCache();
		}
		catch (Market\Exceptions\Api\ObjectPropertyException $exception)
		{
			$parameter = $exception->getParameter();
			$message = $this->getObjectPropertyEmptyMessage($parameter) ?: $exception->getMessage();

			throw new Main\ArgumentException($message, $parameter, $exception);
		}

		return [
			'status' => 'ok',
			'message' => $setup->getService()->getInfo()->getMessage(
				'SHIPMENT_SUBMIT_SUCCESS',
				null,
				static::getLang('ADMIN_SHIPMENT_SUBMIT_SHIPMENT_SUCCESS')
			),
		];
	}

	protected function needCheckAccess()
	{
		return !Market\Ui\Access::isWriteAllowed();
	}

	protected function loadLocalOrder(Market\Trading\Setup\Model $setup, $externalId)
	{
		$environment = $setup->getEnvironment();
		$platform = $setup->getPlatform();
		$orderRegistry = $environment->getOrderRegistry();
		$localId = $orderRegistry->search($externalId, $platform, false);

		if ($localId === null)
		{
			$message = static::getLang('ADMIN_SHIPMENT_SUBMIT_LOCAL_ORDER_NOT_EXISTS');
			throw new Main\ObjectNotFoundException($message);
		}

		return $orderRegistry->loadOrder($localId);
	}

	protected function checkOrderAccess(Market\Trading\Entity\Reference\Order $order)
	{
		global $USER;

		$userId = $USER->GetID();

		if (!$order->hasAccess($userId, Market\Trading\Entity\Operation\Order::BOX))
		{
			$message = static::getLang('ADMIN_SHIPMENT_SUBMIT_LOCAL_ORDER_DENIED');
			throw new Main\AccessDeniedException($message);
		}
	}

	protected function submitBoxes(Market\Trading\Setup\Model $setup, ShipmentRequest\Request $requestShipment)
	{
		$procedure = new Market\Trading\Procedure\Runner(
			Market\Trading\Entity\Registry::ENTITY_TYPE_ORDER,
			$requestShipment->getAccountNumber()
		);

		try
		{
			$procedure->run($setup, 'send/boxes', [
				'orderId' => $requestShipment->getOrderId(),
				'orderNum' => $requestShipment->getAccountNumber(),
				'shipmentId' => $requestShipment->getShipmentId(),
				'boxes' => $this->makeBoxes($requestShipment),
			]);
		}
		catch (Market\Exceptions\Api\Request $exception)
		{
			$procedure->logException($exception);
			throw $exception;
		}
	}

	protected function makeBoxes(ShipmentRequest\Request $requestShipment)
	{
		$result = [];

		/** @var ShipmentRequest\Box $box */
		foreach ($requestShipment->getBoxes() as $box)
		{
			$outgoingBox = [
				'fulfilmentId' => $box->getFulfilmentId(),
				'weight' => $box->getSize('WEIGHT'),
				'width' => $box->getSize('WIDTH'),
				'height' => $box->getSize('HEIGHT'),
				'depth' => $box->getSize('DEPTH'),
			];

			$result[] = $outgoingBox;
		}

		return $result;
	}

	protected function flushOrderCache()
	{
		Market\Trading\State\SessionCache::releaseByType('order');
	}

	public function show()
	{
		throw new Main\NotSupportedException();
	}

	protected function getRequestShipment()
	{
		$shipmentList = $this->request->getPost('YAMARKET_SHIPMENT');

		if (!is_array($shipmentList))
		{
			$message = static::getLang('ADMIN_SHIPMENT_SUBMIT_SHIPMENT_MUST_BE_ARRAY');
			throw new Main\SystemException($message);
		}

		$data = isset($shipmentList['ID']) ? $shipmentList : (array)reset($shipmentList);

		return new ShipmentRequest\Request($data);
	}

	protected function getObjectPropertyEmptyMessage($parameter)
	{
		list($fields, $variables) = $this->splitObjectProperty($parameter);

		$code = implode('_', $fields);

		return static::getLang('ADMIN_SHIPMENT_SUBMIT_FIELD_EMPTY_' . $code, $variables);
	}

	protected function splitObjectProperty($parameter)
	{
		$parts = explode('.', $parameter);
		$fields = [];
		$variables = [];

		foreach ($parts as $part)
		{
			if (preg_match('/^(.*?)\[(\d+)]$/', $part, $matches))
			{
				$field = $matches[1];
				$index = (int)$matches[2];

				$variables['#' . $field . '_NUMBER#'] = $index + 1;
			}
			else
			{
				$field = $part;
			}

			$fields[] = $field;
		}

		return [$fields, $variables];
	}
}