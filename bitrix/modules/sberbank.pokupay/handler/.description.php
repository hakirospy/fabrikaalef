<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\PaySystem;
Loc::loadMessages(__FILE__);
// $description = array(
// 	'MAIN' => Loc::getMessage('SBERBANK_POKUPAY_MODULE_TITLE'),
// );
$data = array(
	'NAME' => Loc::getMessage("SBERBANK_POKUPAY_MODULE_TITLE"),
	// 'DESCRIPTION' => Loc::getMessage("SBERBANK_POKUPAY_MODULE_TITLE"),
	'SORT' => 100,
	'CODES' => array(
		"SBERBANK_POKUPAY_API_LOGIN" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_API_LOGIN_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_API_LOGIN_DESCR"),
			'SORT' => 100,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_GATE"),
		),
		"SBERBANK_POKUPAY_API_PASSWORD" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_API_PASSWORD_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_API_PASSWORD_DESCR"),
			'SORT' => 120,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_GATE"),
		),
		"SBERBANK_POKUPAY_API_TEST_MODE" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_API_TEST_MODE_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_API_TEST_MODE_DESCR"),
			'SORT' => 130,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_GATE"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"SBERBANK_POKUPAY_API_RETURN_URL" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_API_RETURN_URL_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_API_RETURN_URL_DESCR"),
			'SORT' => 670,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_GATE"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => ''
			)
		),
		"SBERBANK_POKUPAY_API_FAIL_URL" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_API_FAIL_URL_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_API_FAIL_URL_DESCR"),
			'SORT' => 680,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_GATE"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => ''
			)
		),
		"SBERBANK_POKUPAY_HANDLER_LOGGING" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_HANDLER_LOGGING_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_HANDLER_LOGGING_DESCR"),
			'SORT' => 250,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_HANDLER"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
            	"PROVIDER_KEY" => "INPUT"
			)
		),

		"SBERBANK_POKUPAY_HANDLER_SHIPMENT" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_HANDLER_SHIPMENT_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_HANDLER_SHIPMENT_DESCR"),
			'SORT' => 320,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_HANDLER"),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"SBERBANK_POKUPAY_CREDIT_TYPE" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_TYPE_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_TYPE_DESCR"),
			'SORT' => 210,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_CREDIT"),
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					"CREDIT" => Loc::getMessage("SBERBANK_POKUPAY_TYPE_VALUE_1"), // Кредит
	                "INSTALLMENT" => Loc::getMessage("SBERBANK_POKUPAY_TYPE_VALUE_2"), // Кредит без переплаты
	                
				)
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "CREDIT",
            	"PROVIDER_KEY" => "INPUT"
			)
		),
		"SBERBANK_POKUPAY_CREDIT_MAX_MONTH" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_MAX_MONTH_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_MAX_MONTH_DESCR"),
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_CREDIT"),
			'SORT' => 220,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "12",
			)
		),
		"SBERBANK_POKUPAY_ORDER_NUMBER" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_NUMBER_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_NUMBER_DESCR"),
			'SORT' => 650,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"SBERBANK_POKUPAY_ORDER_AMOUNT" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_AMOUNT_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_AMOUNT_DESCR"),
			'SORT' => 660,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"SBERBANK_POKUPAY_ORDER_DESCRIPTION" => array(
			"NAME" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_DESCRIPTION_NAME"),
			"DESCRIPTION" => Loc::getMessage("SBERBANK_POKUPAY_ORDER_DESCRIPTION_DESCR"),
			'SORT' => 670,
			'GROUP' => Loc::getMessage("SBERBANK_POKUPAY_GROUP_ORDER"),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'USER_DESCRIPTION'
			)
		),


	)
);

$app = \Bitrix\Main\Application::getInstance();
$request = $app->getContext()->getRequest();

if($request->isPost() && $request->getPost('ACTION_FILE') == 'sberbank_pokupay' && isset($_POST['PAYSYSTEMBizVal']) ) {

	$SBERBANK_Gateway = new \Sberbank\Credit\Gateway;

	$paySystemParams = $request->getPost('PAYSYSTEMBizVal')['MAP']['PAYSYSTEM_'. $request->getPost('ID')];


	$paySystemParamsLogin = $paySystemParams['SBERBANK_POKUPAY_API_LOGIN'];
	$paySystemParamsPassword = $paySystemParams['SBERBANK_POKUPAY_API_PASSWORD'];
	$paySystemParamsTestmode = $paySystemParams['SBERBANK_POKUPAY_API_TEST_MODE'];

	if(!isset($paySystemParamsLogin[0]['DELETE'])) {
		$SBERBANK_Gateway->buildData(array(
			'login' => $paySystemParamsLogin[0]['PROVIDER_VALUE'],
			'name' =>  str_replace('-api', "", $paySystemParamsLogin[0]['PROVIDER_VALUE'])
		));
	}
	if(!isset($paySystemParamsPassword[0]['DELETE'])) {
		$SBERBANK_Gateway->buildData( array('password' => $paySystemParamsPassword[0]['PROVIDER_VALUE']) );
	}
	if(!isset($paySystemParamsTestmode[0]['DELETE'])) {
		$SBERBANK_Gateway->setOptions( array('test_mode' => $paySystemParamsTestmode[0]['PROVIDER_VALUE'] == 'Y' ? 1 : 0 ) );
	} else {
		$SBERBANK_Gateway->setOptions( array('test_mode' => 0) );
	}

	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off" ? 'https://' : 'http://';
	$domain_name = $_SERVER['HTTP_HOST'];

	$SBERBANK_Gateway->buildData(array(
		'callbacks_enabled' => true,
		'callback_addresses' => $protocol . $domain_name . '/sberbank/credit_result.php',
	));
	$SBERBANK_Gateway->updateCallback($callbackUrl);
}