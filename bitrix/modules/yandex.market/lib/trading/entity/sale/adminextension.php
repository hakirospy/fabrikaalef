<?php

namespace Yandex\Market\Trading\Entity\Sale;

use Yandex\Market;
use Bitrix\Main;

class AdminExtension extends Market\Trading\Entity\Reference\AdminExtension
{
	protected static $isTabInitialized = false;

	public static function OnAdminContextMenuShow(&$items)
	{
		$request = Main\Context::getCurrent()->getRequest();

		if (!static::$isTabInitialized && static::isOrderPage($request))
		{
			$orderId = (int)$request->get('ID');
			$contextItem = static::getContextMenuItem([ 'ID' => $orderId ]);

			if ($contextItem !== null)
			{
				array_splice($items, 1, 0, [ $contextItem ]);
			}
		}
	}

	public static function OnAdminSaleOrderView($parameters)
	{
		return static::initializeOrderTab($parameters);
	}

	public static function OnAdminSaleOrderEdit($parameters)
	{
		return static::initializeOrderTab($parameters);
	}

	protected static function initializeOrderTab($parameters)
	{
		try
		{
			$tabSet = static::createTabSet($parameters);

			$tabSet->checkReadAccess();
			$tabSet->checkSupport();

			$result = $tabSet->initialize();
			static::$isTabInitialized = true;
		}
		catch (Main\SystemException $exception)
		{
			$result = null;
		}

		return $result;
	}

	protected static function getContextMenuItem($parameters)
	{
		try
		{
			$tabSet = static::createTabSet($parameters);

			$tabSet->checkReadAccess();
			$tabSet->checkSupport();
			$tabSet->preloadAssets();

			$actionParams = [
				'content_url' => $tabSet->getContentsUrl(),
				'title' => $tabSet->getTitle(),
				'draggable' => true,
				'resizable' => true,
				'width' => 1024,
				'height' => 750,
			];
			$actionMethod = '(new BX.CAdminDialog(' . \CUtil::PhpToJSObject($actionParams) . ')).Show();';

			$result = [
				'TEXT' => $tabSet->getNavigationTitle(),
				'LINK' => 'javascript:' . $actionMethod,
			];
		}
		catch (Main\SystemException $exception)
		{
			$result = null;
		}

		return $result;
	}

	protected static function createTabSet($parameters)
	{
		$orderId = static::extractParametersOrderId($parameters);
		$tradingInfo = static::getTradingInfo($orderId);
		$order = static::getOrder($orderId);
		$setup = Market\Trading\Setup\Model::loadByExternalIdAndSite($tradingInfo['TRADING_PLATFORM_ID'], $order->getSiteId());

		return new Market\Ui\Trading\OrderViewTabSet($setup, $tradingInfo['EXTERNAL_ORDER_ID']);
	}

	protected static function extractParametersOrderId($parameters)
	{
		if (!isset($parameters['ID']))
		{
			throw new Main\ArgumentException('parameters hasn\'t id');
		}

		return (int)$parameters['ID'];
	}

	protected static function getTradingInfo($orderId)
	{
		$platformRow = OrderRegistry::searchPlatform($orderId);

		if ($platformRow === null)
		{
			throw new Main\ObjectNotFoundException('trading order not registered');
		}

		return $platformRow;
	}

	protected static function getOrder($orderId)
	{
		$environment = Market\Trading\Entity\Manager::createEnvironment();

		return $environment->getOrderRegistry()->loadOrder($orderId);
	}

	protected static function isOrderPage(Main\HttpRequest $request)
	{
		$pageUrl = $request->getRequestedPage();

		return (
			$pageUrl === BX_ROOT . '/admin/sale_order_view.php'
			|| $pageUrl === BX_ROOT . '/admin/sale_order_edit.php'
		);
	}

	protected function getEventHandlers()
	{
		return [
			[
				'module' => 'main',
				'event' => 'OnAdminSaleOrderView',
			],
			[
				'module' => 'main',
				'event' => 'OnAdminSaleOrderEdit',
			],
			[
				'module' => 'main',
				'event' => 'OnAdminContextMenuShow',
			],
		];
	}
}
