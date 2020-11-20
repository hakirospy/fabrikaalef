<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("sberbank.pokupay");
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sberbank.pokupay");
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("SBERBANK_POKUPAY_SO_ACCESS_DENIED"));

$sTableID = "rbs_credit_orders";

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

if ($APPLICATION->GetGroupRight("sberbank.pokupay") != "D")
{

  function CheckFilter() {
      global $FilterArr, $lAdmin;
      foreach ($FilterArr as $f)
          global $$f;

      return count($lAdmin->arFilterErrors) == 0;
  }

  $FilterArr = Array(
    "find_id",
    "find_sbrf_id",
    "find_date_create",
    "find_date_update",
    "find_products",
    "find_payed",
    "find_sum",
  );

  $lAdmin->InitFilter($FilterArr);

  if (CheckFilter())
  {
      $arFilter = array(
        "ID" => "find_id",
        "SBRF_ID" => "find_sbrf_id",
        "DATE_CREATE" => "find_date_create",
        "DATE_UPDATE" => "find_date_update",
        "PRODUCTS" => "find_products",
        "PAYED" => "find_payed",
        "SUM" => "find_sum",
      );
  }

  if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W")
  {
      if ($_REQUEST['action_target'] == 'selected')
      {

          $rsData = $DB->Query(' SELECT * FROM `'.$sTableID.'`', false, " " . __LINE__);
          while ($arRes = $rsData->Fetch())
              $arID[] = $arRes['ID'];
      }

      foreach ($arID as $ID)
      {
          if (strlen($ID) <= 0)
              continue;
          $ID = IntVal($ID);

          switch ($_REQUEST['action']) {
              case "delete":
                  @set_time_limit(0);
                  $DB->StartTransaction();

                  if (!$DB->Query('delete FROM `'.$sTableID.'` where ID=' . $ID, false, " " . __LINE__))
                  {
                      $DB->Rollback();
                  }
                  $DB->Commit();
                  break;

              case "activate":
              case "deactivate":


                  $act = ($_REQUEST['action'] == "activate" ? "Y" : "N");

                  $DB->Query("UPDATE `'.$sTableID.'` SET `ACTIVE` = '" . $act . "' WHERE `'.$sTableID.'`.`ID`=" . $ID, false, " " . __LINE__);


                  break;
          }
      }
  }

  if($lAdmin->EditAction())
  {
  	foreach($FIELDS as $id=>$arField) {
              $ID = IntVal($ID);
  		if($ID <= 0)
  			continue;
  	}
  }

  $rsData = $DB->Query('SELECT * FROM `'.$sTableID.'` ORDER BY `'.$by.'` '.$order, false, " " . __LINE__);

  $rsData = new CAdminResult($rsData, $sTableID);

  $rsData->NavStart();

  $lAdmin->NavText($rsData->GetNavPrint(GetMessage("SBERBANK_POKUPAY_SO_ORDERS")));

  $lAdmin->AddHeaders(array(
    array("id" => "ID",
      "content" => "ID",
      "sort" => "ID",
      "default" => true,
    ),
    array("id" => "CMS_ORDER_ID",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_CMS_ORDER_ID"),
      "sort" => "CMS_ORDER_ID",
      "default" => true,
    ),
    array("id" => "CMS_PAYMENT_ID",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_CMS_PAYMENT_ID"),
      "sort" => "CMS_PAYMENT_ID",
      "default" => true,
    ),
    array("id" => "PAYMENT_SUM",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_PAYMENT_SUM"),
      "sort" => "PAYMENT_SUM",
      "default" => true,
    ),
    array("id" => "BANK_ORDER_ID",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_BANK_ORDER_ID"),
      "sort" => "BANK_ORDER_ID",
      "default" => true,
    ),
    array("id" => "BANK_SUM",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_BANK_SUM"),
      "sort" => "BANK_SUM",
      "default" => true,
    ),
    array("id" => "BANK_ORDER_STATUS",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_BANK_ORDER_STATUS"),
      "sort" => "BANK_ORDER_STATUS",
      "default" => true,
    ),
    // array("id" => "CMS_ORDER_STATUS",
    //   "content" => GetMessage("SBERBANK_POKUPAY_SO_CMS_ORDER_STATUS"),
    //   "sort" => "CMS_ORDER_STATUS",
    //   "default" => true,
    // ),
    array("id" => "USER_INFO",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_USER_INFO"),
      "sort" => "USER_INFO",
      "default" => true,
    ),
    array("id" => "DATE_CREATE",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_DATE_CREATE"),
      "sort" => "DATE_CREATE",
      "default" => true,
    ),
    array("id" => "DATE_UPDATE",
      "content" => GetMessage("SBERBANK_POKUPAY_SO_DATE_UPDATE"),
      "sort" => "DATE_UPDATE",
      "default" => true,
    ),
  ));

  while($arRes = $rsData->NavNext(true, "f_")) {
      $row = & $lAdmin->AddRow($f_ID, $arRes);
  	$row->AddField("ID", $f_ID);
    if(isset($arRes['BANK_ORDER_STATUS'])) {
  	 $row->AddViewField("BANK_ORDER_STATUS", GetMessage("SBERBANK_POKUPAY_SO_ORDER_STATUS_" . $arRes['BANK_ORDER_STATUS']));
    }
    $row->AddViewField("CMS_ORDER_ID", '<a href="/bitrix/admin/sale_order_view.php?ID='.$arRes['CMS_ORDER_ID'].'&lang='.LANG.'">'.$arRes['CMS_ORDER_ID'].'</a>');
    $row->AddViewField("CMS_PAYMENT_ID", '<a href="/bitrix/admin/sale_order_payment_edit.php?order_id='.$arRes['CMS_ORDER_ID'].'&payment_id='. $arRes['CMS_PAYMENT_ID'] .'&lang='.LANG.'">'.$arRes['CMS_PAYMENT_ID'].'</a>');
  	$arActions[] = array(
      "ICON" => "view",
      "TEXT" => GetMessage("SBERBANK_POKUPAY_SO_GO_ORDER"),
      "ACTION" => $lAdmin->ActionRedirect('/bitrix/admin/sale_order_view.php?ID='.$arRes['CMS_ORDER_ID'].'&lang='.LANG),
      "DEFAULT" => true
    );
    $arActions[] = array(
      "ICON" => "view",
      "TEXT" => GetMessage("SBERBANK_POKUPAY_SO_GO_PAYMENT"),
      "ACTION" => $lAdmin->ActionRedirect('/bitrix/admin/sale_order_payment_edit.php?order_id='.$arRes['CMS_ORDER_ID'].'&payment_id='. $arRes['CMS_PAYMENT_ID'] .'&lang='.LANG),
      "DEFAULT" => true
    );
  	// $arActions[] = array(
  	// 	"ICON" => "delete",
  	// 	"TEXT" => GetMessage("SBERBANK_POKUPAY_SO_DELETE"),
  	// 	"ACTION" => "if(confirm('".GetMessage("SBERBANK_POKUPAY_SO_DELETE_CONFIRM")."?')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
  	// 	"DEFAULT" => false
   //    );
  	$row->AddActions($arActions);
  unset($arActions);

  }

  $lAdmin->AddFooter(
      array(
        array("title" => GetMessage("SBERBANK_POKUPAY_SO_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
        array("counter" => true, "title" => GetMessage("SBERBANK_POKUPAY_SO_ADMIN_LIST_SELECTED"), "value" => "0"),
      )
  );


  // $lAdmin->AddGroupActionTable(Array(
  //   "delete" => GetMessage("SBERBANK_POKUPAY_SO_ADMIN_LIST_DELETE"),
  // ));


  $aContext = array(
    // array(
    //   "TEXT" => GetMessage("SBERBANK_POKUPAY_SO_ADD_ORDER"),
    //   "LINK" => "/url/,
    //   "TITLE" => GetMessage("SBERBANK_POKUPAY_SO_ADD_ORDER"),
    //   "ICON" => "btn_new",
    // ),
  );

  $lAdmin->AddAdminContextMenu($aContext);

  $lAdmin->CheckListMode();

  $APPLICATION->SetTitle(GetMessage("SBERBANK_POKUPAY_SO_ORDERS_LIST"));

  require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

  $oFilter = new CAdminFilter(
    $sTableID . "_filter", array(
    "ID",
    GetMessage("SBERBANK_POKUPAY_SO_SBRF_ID"),
    GetMessage("SBERBANK_POKUPAY_SO_DATE_CREATE"),
    GetMessage("SBERBANK_POKUPAY_SO_DATE_UPDATE"),
    GetMessage("SBERBANK_POKUPAY_SO_PRODUCTS"),
    GetMessage("SBERBANK_POKUPAY_SO_PAYED"),
    GetMessage("SBERBANK_POKUPAY_SO_SUM"),
      )
  );

  $lAdmin->DisplayList();
  ?>

  <?
  require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

}
?>