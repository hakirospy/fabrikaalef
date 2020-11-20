<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::includeModule("ipol.kladr");
$result=CKladr::getBitrixLocationCodeByName();
echo json_encode(CKladr::zajsonit($result), JSON_UNESCAPED_UNICODE);
?>