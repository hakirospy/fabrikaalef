<?php

use Bitrix\Main;

define('NO_AGENT_CHECK', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('DisableEventsCheck', true);

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_before.php';

$request = Main\Context::getCurrent()->getRequest();
$requestPage = $request->getRequestedPage();
$requestPage = preg_replace('#/index\.php$#', '/', $requestPage);
$sefFolder = BX_ROOT . '/services/yandex.market/trading/';
$serviceCode = null;
$siteId = SITE_ID;

if (preg_match('#^' . $sefFolder .'([\w\d-]+)(?:/|$)(?:([\w\d\-]{2})(?:/|$))?#', $requestPage, $matches))
{
	$serviceCode = $matches[1];
	$sefFolder .= $serviceCode . '/';

	if (isset($matches[2]))
	{
		$siteId = $matches[2];
		$sefFolder .= $siteId . '/';
	}
}

$APPLICATION->IncludeComponent('yandex.market:purchase', '', [
	'SEF_FOLDER' => $sefFolder,
	'SERVICE_CODE' => $serviceCode,
	'SITE_ID' => $siteId,
], false, [ 'HIDE_ICONS' => 'Y' ]);

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_after.php';