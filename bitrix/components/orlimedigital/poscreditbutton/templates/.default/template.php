<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CJSCore::Init(array('jquery'));

Bitrix\Main\Page\Asset::getInstance()->addString('<link href="//api.b2pos.ru/shop/connect.css" type="text/css" rel="stylesheet" />');
Bitrix\Main\Page\Asset::getInstance()->addString('<script src="//api.b2pos.ru/shop/connect.js" charset="utf-8" type="text/javascript"></script>');
?>

<button id="poscredit_oneclick_button" class="<?=$arParams['CREDIT_BTN_CLASS']?>"><?=$arParams['CREDIT_BTN_NAME']?></button>

<script type="text/javascript">
window.poscreditAccessID = 0;
window.poscreditTradeID = 0;
window.poscreditOrderNum = "";
window.poscreditUrl = '/bitrix/admin/orlimedigital.poscredit_ajax.php?<?=bitrix_sessid_get()?>';
window.poscreditData = <?=CUtil::PhpToJSObject($arResult['DATA'], false, true)?>;
window.poscreditSettings = <?=CUtil::PhpToJSObject($arResult['PARAMS'], false, true)?>;

var productsList = new Array();

productsList[0] = { id: '<?=$arResult['DATA']['ORDER']['PRODUCT_ID']?>', name: '<?=$arResult['DATA']['ORDER']['NAME']?>', category: '&nbsp;', price: '<?=$arResult['DATA']['ORDER']['PRICE']?>', count: '<?=$arResult['DATA']['ORDER']['QUANTITY']?>' };

function poscredit_init() {
	poscreditServices('creditProcess', window.poscreditAccessID, { order: window.poscreditOrderNum, products: productsList, phone: window.poscreditData['ORDER']['PHONE'], tradeID: window.poscreditTradeID }, function(result){
		if(result.success === false) {
			alert('Error with connected to form...');
		}
	});
}

function poscreditSaveProfile(profileID, fullName, mobilePhone) {
	window.poscreditData['ACTION'] = "CREATE_ORDER";
	window.poscreditData['ORDER']['PAY_ID'] = profileID;
	window.poscreditData['ORDER']['FIO'] = fullName;
	window.poscreditData['ORDER']['PHONE'] = mobilePhone;
	$.ajax({
		url: window.poscreditUrl,
		cache: false,
		dataType: "json",
		data: window.poscreditData,
		success: function(result) {
			
		}
	});
}
</script>