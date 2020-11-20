<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

use Bitrix\Main,
    Bitrix\Main\Config\Option,
    Bitrix\Sale\Delivery,
    Bitrix\Sale\PaySystem,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Order;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$lang = ($request->get('lang') !== null) ? trim($request->get('lang')) : "ru";
\Bitrix\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array("ERROR" => "");

if(!\Bitrix\Main\Loader::includeModule('sale'))
    $arResult["ERROR"] = "Error! Can't include module \"Sale\"";

if(!\Bitrix\Main\Loader::includeModule('orlimedigital.poscredit'))
    $arResult["ERROR"] = "Error! Can't include module \"orlimedigital.poscredit\"";

if($arResult["ERROR"] === '' && check_bitrix_sessid()) {
	$action = ($request->get('ACTION') !== null) ? trim($request->get('ACTION')) : '';
	$order = ($request->get('ORDER') !== null) ? $request->get('ORDER') : false;
	
	if($action == "CREATE_ORDER") {
		try {
			$data = $order;
			$siteId = $data['SITE_ID'] ? $data['SITE_ID']: 's1';
			$currencyCode = Option::get('sale', 'default_currency', 'RUB');

			if($USER->getId()) {
				$userId = $USER->getId();
			} else {
				$userId = \CSaleUser::GetAnonymousUserID();
			}

			$orderIdFromRequest = false;
			$order = false;

			if($data['ID'] && $data['ID'] != '') {
				$orderIdFromRequest = intval($data['ID']);
			}
			if($orderIdFromRequest) {
				$order = Order::loadByAccountNumber($orderIdFromRequest);
				if(!$order)
					$order = Order::load($orderIdFromRequest);
			}
			if(!$order)
				$order = Order::create($siteId, $userId);

			if(is_array($data) && $data['PRODUCT_ID'] && $data['IBLOCK_ID']) {
				$basket = Basket::create($siteId);
				$product = $basket->createItem($data['IBLOCK_ID'], $data['PRODUCT_ID']);
				$product->setFields(array(
					'NAME' => $data['NAME'],
					'PRICE' => doubleval($data['PRICE']),
					'CURRENCY' => $currencyCode,
					'QUANTITY' => intval($data['QUANTITY']) > 0 ? $data['QUANTITY'] : 1,
				));
				$order->setPersonTypeId(intval($data['PERSONTYPE']) > 0? $data['PERSONTYPE']: 1);
				$order->setBasket($basket);

				$shipmentCollection = $order->getShipmentCollection();
				$service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());

				$shipment = $shipmentCollection->createItem(
				   Delivery\Services\Manager::getObjectById($service['ID'])
				);

				$shipmentItemCollection = $shipment->getShipmentItemCollection();
				$shipmentItem = $shipmentItemCollection->createItem($product);
				$shipmentItem->setQuantity($product->getQuantity());

				$paySystemId = intval($data['PAY_SYSTEM_ID']) > 0 ? $data['PAY_SYSTEM_ID'] : 1;

				$paymentCollection = $order->getPaymentCollection();
				$payment = $paymentCollection->createItem();
				$paySystemService = PaySystem\Manager::getObjectById($paySystemId);

				$payment->setFields(array(
					'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
					'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
				));

				$payment->setField("SUM", $order->getPrice());
				$payment->setField("CURRENCY", $order->getCurrency());

				$propertyCollection = $order->getPropertyCollection();
				$phoneProp = $propertyCollection->getPhone();
				$phoneProp->setValue($data['PHONE'] ? $data['PHONE'] : '');
				$nameProp = $propertyCollection->getPayerName();
				$nameProp->setValue($data['FIO'] ? $data['FIO'] : '');
				$order->save();

				if($data['PAY_ID'] && $data['PAY_ID'] != '') {
					$arPaymentsCollection = $order->loadPaymentCollection();
					$currentPaymentOrder = $arPaymentsCollection->current();
					do {
						$currentPaymentOrder->setField('PS_STATUS_CODE', 'WORK');
						$currentPaymentOrder->setField('PS_STATUS_DESCRIPTION', GetMessage("PS_CUSTOMER_SENT_ORDER")  . $data['PAY_ID']);
						$currentPaymentOrder->setField('PS_STATUS_MESSAGE', $data['PAY_ID']);
						$currentPaymentOrder->save();

					} while ($currentPaymentOrder = $arPaymentsCollection->next());

					$order->setField('ADDITIONAL_INFO', GetMessage("PS_CUSTOMER_SENT_ORDER") . $data['PAY_ID']);
					$order->save();
				}

				$order->save();
			} else {
				return false;
			}
		} catch (Exception $e) {
			$arResult["ERROR"] = "Error! Create order - error";
		}
		
	} else if($action == "GET_SETTINGS") {
		$arResult["settings"]["accessID"] = Option::get("orlimedigital.poscredit", "POSCREDIT_ACCESSID");
		$arResult["settings"]["tradeID"] = Option::get("orlimedigital.poscredit", "POSCREDIT_TRADEID");
	}
	
} else {
    if(strlen($arResult["ERROR"]) <= 0)
        $arResult["ERROR"] = "Error! Access denied";
}

if(strlen($arResult["ERROR"]) > 0)
    $arResult["RESULT"] = "ERROR";
else
    $arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
    $arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));