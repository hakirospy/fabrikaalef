<?
IncludeModuleLangFile(__FILE__);
if ($APPLICATION->GetGroupRight("sberbank.pokupay") != "D")
{

    $aMenu = array(
        "parent_menu" => "global_menu_store",
        "sort" => 110,
        "text" => GetMessage("SBERBANK_POKUPAY_SERVICE_MODULE_NAME"),
        "icon" => "sberbank_pokupay_menu_icon",
        "page_icon" => "sberbank_pokupay_page_icon",
        "items_id" => "sberbank_pokupay",
        "url" => "/bitrix/admin/sberbank_pokupay_orders_list.php",
        "items" => array(
            array(
                "text" => GetMessage("SBERBANK_POKUPAY_SERVICE_ORDERS"),
                "url" => "/bitrix/admin/sberbank_pokupay_orders_list.php",
                // "more_url" => array('/bitrix/admin/sberbank_pokupay_order_edit.php')
            ),

        ),
    );

    if ($APPLICATION->GetGroupRight("main") == "W")
    {
         $aMenu['items'][] = array(
            "text" => GetMessage("SBERBANK_POKUPAY_SERVICE_SETINGS"),
            "url" => "/bitrix/admin/settings.php?lang=". LANGUAGE_ID ."&mid=sberbank.pokupay",
        ); 
    }

    return $aMenu;
}