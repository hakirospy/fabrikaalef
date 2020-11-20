<?php
use Bitrix\Main\Loader;

require dirname(__FILE__) ."/config.php";

Loader::registerAutoLoadClasses(
	$SBERBANK_CONFIG['MODULE_ID'],
	array(
        '\Sberbank\Credit\Gateway' => 'lib/rbs/Gateway.php',
        '\Sberbank\Credit\Orders' => 'lib/rbs/Orders.php',
	)
);
?>