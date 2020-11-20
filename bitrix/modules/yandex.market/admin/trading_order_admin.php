<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Yandex\Market;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_TITLE'));

$controller = null;
$state = null;
$serviceCode = null;

try
{
	if (!Main\Loader::includeModule('yandex.market'))
	{
		$message = Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_REQUIRE_MODULE');
		throw new Main\SystemException($message);
	}

	if (!Market\Ui\Access::isProcessTradingAllowed())
	{
		$message = Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_ACCESS_DENIED');
		throw new Main\SystemException($message);
	}

	$request = Main\Context::getCurrent()->getRequest();
	$serviceCode = (string)$request->getQuery('service');

	if ($serviceCode === '')
	{
		$message = Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_UNDEFINED');
		throw new Main\SystemException($message);
	}

	$setupCollection = Market\Trading\Setup\Collection::loadByService($serviceCode);
	$setup = $setupCollection->getActive();

	if ($setup === null)
	{
		$message = Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SETUP_NOT_FOUND');
		throw new Main\ObjectNotFoundException($message);
	}

	if (!$setup->isActive())
	{
		$message = Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SETUP_INACTIVE');
		throw new Main\SystemException($message);
	}

	$platform = $setup->getPlatform();
	$orderRegistry = $setup->getEnvironment()->getOrderRegistry();
	$url = $orderRegistry->getAdminListUrl($platform);

	LocalRedirect($url);
	die();
}
catch (Main\ObjectNotFoundException $exception)
{
	$isHandled = false;

	if (Market\Trading\Service\Migration::isDeprecated($serviceCode))
	{
		$isHandled = true;
		$migrationCode = Market\Trading\Service\Migration::getDeprecateUse($serviceCode);
		$migrationUrl = $APPLICATION->GetCurPageParam(http_build_query([ 'service' => $migrationCode ]), [ 'service' ]);

		\CAdminMessage::ShowMessage([
			'TYPE' => 'ERROR',
			'MESSAGE' => Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_DEPRECATED'),
			'DETAILS' => Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_DEPRECATED_DETAILS', [
				'#URL#' => $migrationUrl,
			]),
			'HTML' => true,
		]);
	}
	else if (Market\Trading\Service\Migration::hasMigrated($serviceCode))
	{
		$migratedCodes = Market\Trading\Service\Migration::getMigrated($serviceCode);
		$migratedCollection = Market\Trading\Setup\Collection::loadByService($migratedCodes);
		$migratedSetup = $migratedCollection->getActive();

		if ($migratedSetup !== null)
		{
			$isHandled = true;
			$setupUrl = Market\Ui\Admin\Path::getModuleUrl('trading_edit', [
				'lang' => LANGUAGE_ID,
				'service' => $serviceCode,
			]);
			$oldUrl = $APPLICATION->GetCurPageParam(
				http_build_query([ 'service' => $migratedSetup->getServiceCode() ]),
				[ 'service' ]
			);

			\CAdminMessage::ShowMessage([
				'TYPE' => 'ERROR',
				'MESSAGE' => Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_NEED_MIGRATE'),
				'DETAILS' => Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_NEED_MIGRATE_DETAILS', [
					'#URL#' => $setupUrl,
				]),
				'HTML' => true,
			]);

			echo sprintf(
				'<a class="adm-btn" href="%s">%s</a><br /><br />',
				$oldUrl,
				Loc::getMessage('YANDEX_MARKET_TRADING_ORDERS_SERVICE_FALLBACK_DEPRECATED')
			);
		}
	}

	if (!$isHandled)
	{
		\CAdminMessage::ShowMessage([
			'TYPE' => 'ERROR',
			'MESSAGE' => $exception->getMessage(),
		]);
	}
}
catch (Main\SystemException $exception)
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => $exception->getMessage(),
	]);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
