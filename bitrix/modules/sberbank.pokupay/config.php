<?php

include dirname(__FILE__) . "/install/version.php";

$SBERBANK_CONFIG = Array(
	'MODULE_ID' => 'sberbank.pokupay',
	'BANK_NAME' => 'Sberbank',
	'SBERBANK_PROD_URL' => 'https://securepayments.sberbank.ru/payment/rest/',
	'SBERBANK_PROD_URL_С' => 'https://securepayments.sberbank.ru/sbercredit/',
	'SBERBANK_TEST_URL' => 'https://3dsec.sberbank.ru/payment/rest/',
	'SBERBANK_TEST_URL_С' => 'https://3dsec.sberbank.ru/sbercredit/',
	'ISO' => array(
	    'USD' => 840,
	    'EUR' => 978,
	    'RUB' => 643,
	    'RUR' => 643,
	    'BYN' => 933
	),
	'MODULE_VERSION' => $arModuleVersion['VERSION'],
);

