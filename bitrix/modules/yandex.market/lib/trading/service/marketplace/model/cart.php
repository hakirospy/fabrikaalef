<?php

namespace Yandex\Market\Trading\Service\Marketplace\Model;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;

class Cart extends Market\Api\Model\Cart
{
	/**
	 * @return Cart\ItemCollection
	 * @throws Main\ObjectPropertyException
	 */
	public function getItems()
	{
		return $this->getRequiredCollection('items');
	}

	protected function getChildCollectionReference()
	{
		return [
			'items' => Cart\ItemCollection::class,
		];
	}
}