<?php
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
include(GetLangFileName(dirname(__FILE__) . '/lang/', '/payment.php'));

if(!CModule::IncludeModule("orlimedigital.poscredit")) {
	$APPLICATION->ThrowException(Loc::getMessage("NO_POSCREDIT_MODULE"));
	return false;
}

\Bitrix\Main\Loader::includeModule('sale');

$basketType = 2;
$specificationList = "";
$referenceNum = "";
$request = Application::getInstance()->getContext()->getRequest();
$orderNumber = preg_replace("/[^0-9]/", '', $request->getQuery("ORDER_ID"));
$iOrderID = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];

$referenceNum = $orderNumber;

$fuser_id = \Bitrix\Sale\Fuser::getId();
if($fuser_id) {
	$fuser_params = \Bitrix\Sale\Internals\FuserTable::getUserById($fuser_id);
	if($fuser_params) {
		$user_params = Bitrix\Main\UserTable::GetByID($fuser_params);
		$arUser = $user_params->Fetch();
		$user_email = $arUser['EMAIL'];
		$user_name = $arUser['NAME'];
		$user_second_name = $arUser['SECOND_NAME'];
		$user_last_name = $arUser['LAST_NAME'];
		$user_mobile = $arUser['PERSONAL_MOBILE'];
	}
}

if(!$iOrderID) {
	$basketType = 1;
}

if($basketType == 1) {
	$dbBasketItems = Sale\Internals\BasketTable::getList(array(
    	'filter' => array('=FUSER_ID' => Sale\Fuser::getId(), '=ORDER_ID' => $orderNumber)
	));

	$order = Sale\Order::loadByAccountNumber($orderNumber);
	$basket = $order->getBasket();
	$productCount = 0;

	foreach($basket as $basketItem) {
		$specificationList .= "productsList[".$productCount."] = { id: '".$basketItem->getField('PRODUCT_ID')."', name: '".$basketItem->getField('NAME')."', category: '&nbsp;', price: '".round($basketItem->getField('PRICE'), 2)."', count: '".(int)$basketItem->getField('QUANTITY')."' };";
		$productCount++;
	}

	$referenceNum = $orderNumber;

} else if($basketType == 2) {
	$dbBasketItems = Sale\Internals\BasketTable::getList(array(
    	'filter' => array('ORDER_ID' => $iOrderID)
	));

	$arBasketItems = [];
	while ($arItems = $dbBasketItems->Fetch()) {
		$arBasketItems[] = $arItems;
	}
	$arOrder = array(
		'SITE_ID' => SITE_ID,
		'USER_ID' => $GLOBALS["USER"]->GetID(),
		'BASKET_ITEMS' => $arBasketItems
	);
	$arOptions = array(
		'COUNT_DISCOUNT_4_ALL_QUANTITY' => "Y",
	);
	$arErrors = array();
	CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);
	$arBasketItems = $arOrder["BASKET_ITEMS"];
	$productCount = 0;
	
	foreach($arBasketItems as $basketItem) {
		$specificationList .= "productsList[".$productCount."] = { id: '".$basketItem['PRODUCT_ID']."', name: '".$basketItem['NAME']."', category: '&nbsp;', price: '".round($basketItem['PRICE'], 2)."', count: '".(int)$basketItem['QUANTITY']."' };";
		$productCount++;
	}

	$referenceNum = $iOrderID;
}
?>

<div style="margin-bottom:30px">
	<p><?=Loc::getMessage('POSCREDIT_WAIT_FOR_CALL')?></p>
	<input type="submit" style="display:block" value="<?=Loc::getMessage('POSCREDIT_SEND_CREDIT_REQUEST_BTN')?>" onclick="poscredit_init()" class="btn btn-default btn-lg" />
</div>

<link href="//api.b2pos.ru/shop/v2/connect.css" type="text/css" rel="stylesheet" />
<script src="//api.b2pos.ru/shop/v2/connect.js" charset="utf-8" type="text/javascript"></script>
<script>
var accessID = "<?=Option::get("orlimedigital.poscredit", "POSCREDIT_ACCESSID")?>";
var tradeID = "<?=Option::get("orlimedigital.poscredit", "POSCREDIT_TRADEID")?>";

var productsList = new Array();

<?php
print $specificationList;
?>

window.onload = function(e) {
	poscredit_init();
}

function poscredit_init() {
	poscreditServices('creditProcess', accessID, { order: '<?=$referenceNum?>', products: productsList, phone: '<?=$user_mobile?>', tradeID: tradeID }, function(result){
		if(result.success === false) {
			alert('Error with connected to form...');
		}
	});
}
</script>