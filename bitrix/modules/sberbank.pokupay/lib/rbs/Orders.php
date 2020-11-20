<?php namespace Sberbank\Credit;

IncludeModuleLangFile(__FILE__);

class Orders {

    var $LAST_ERROR = "";
    var $LAST_MESSAGE = "";
    var $TABLE_NAME = "rbs_credit_orders";

    //get by ID
    function GetByID($ID) {
        global $DB;
        $ID = intval($ID);

        $strSql = "SELECT * " . "FROM " . $this->TABLE_NAME . " WHERE ID='" . $ID . "' ";

        return $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
    }

    function GetByPaymentID($ID) {
        global $DB;
        $ID = intval($ID);

        $strSql = "SELECT * " . "FROM " . $this->TABLE_NAME . " WHERE CMS_PAYMENT_ID='" . $ID . "' ";

        return $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
    }

    //adding
    function Add($arFields, $SITE_ID = SITE_ID) {
        global $DB;

        $arFields["~DATE_CREATE"] = $DB->CurrentTimeFunction();
        $arFields["~DATE_UPDATE"] = $DB->CurrentTimeFunction();

        $ID = $DB->Add($this->TABLE_NAME, $arFields);

        return $ID;
    }

    //Updating record
    function Update($ID, $arFields, $SITE_ID = SITE_ID) {
        global $DB;
        $ID = intval($ID);
        $this->LAST_MESSAGE = "";

        $strUpdate = $DB->PrepareUpdate($this->TABLE_NAME, $arFields);
        if (strlen($strUpdate) > 0)
        {
            $strSql = "UPDATE " .  $this->TABLE_NAME . " SET " .
                $strUpdate . ", " .
                "   DATE_CREATE=" . $DB->GetNowFunction() . " " .
                "," . " DATE_UPDATE=" . $DB->GetNowFunction() . " " .
                "WHERE ID=" . $ID;
            if (!$DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__))
                return false;
        }


        return true;
    }

}

?>
