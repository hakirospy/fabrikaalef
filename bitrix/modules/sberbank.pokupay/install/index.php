<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
IncludeModuleLangFile(__FILE__);

Class sberbank_pokupay extends CModule {

    var $MODULE_ID = 'sberbank.pokupay';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_PATH;
    var $DIR_HANDLERS;

    var $PAYMENT_HANDLER_PATH;

    function __construct() {
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/install/index.php"));
        include($path."/install/version.php");
        include($path."/config.php");
        
        $this->DIR_HANDLERS = strlen(COption::GetOptionString('sale', 'path2user_ps_files')) > 0 ?   COption::GetOptionString('sale', 'path2user_ps_files') : '/bitrix/php_interface/include/sale_payment/';
        if(substr($this->DIR_HANDLERS, 0,1) != '/') {
            $this->DIR_HANDLERS = '/' . $this->DIR_HANDLERS;
        }
        $this->MODULE_PATH = $path;
        $this->MODULE_NAME =  Loc::getMessage('SBERBANK_POKUPAY_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SBERBANK_POKUPAY_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('SBERBANK_POKUPAY_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('SBERBANK_POKUPAY_PARTNER_URI');
        
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PAYMENT_HANDLER_PATH = $_SERVER["DOCUMENT_ROOT"] . $this->DIR_HANDLERS . str_replace(".", "_", $this->MODULE_ID) . "/";

    }
    function changeFiles($files) {

        foreach ($files as $file) {
            if ($file->isDot() === false) {
                $path_to_file = $file->getPathname();
                $file_contents = file_get_contents($path_to_file);
                $file_contents = str_replace("{module_path}", $this->MODULE_ID, $file_contents);
                file_put_contents($path_to_file, $file_contents);
            }
        }
    }
    function InstallFiles($arParams = array()) {

        CopyDirFiles($this->MODULE_PATH . "/install/setup/handler_include", $this->PAYMENT_HANDLER_PATH, true, true);
        CopyDirFiles($this->MODULE_PATH . "/install/setup/sberbank", $_SERVER['DOCUMENT_ROOT'] . '/sberbank/');
        CopyDirFiles($this->MODULE_PATH . "/install/setup/images/logo", $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/sale/sale_payments/');
        CopyDirFiles($this->MODULE_PATH . "/install/setup/images/assets", $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/sberbank.pokupay/');

        // for services
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/setup/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/");
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/setup/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", false, true);

        $this->changeFiles(new DirectoryIterator($this->PAYMENT_HANDLER_PATH));
        $this->changeFiles(new DirectoryIterator($this->PAYMENT_HANDLER_PATH . 'template/'));
    }

    function UnInstallFiles() {
        DeleteDirFilesEx($this->DIR_HANDLERS . str_replace(".", "_", $this->MODULE_ID));
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/". $this->MODULE_ID ."/install/setup/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/". $this->MODULE_ID ."/install/setup/themes/.default/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default");
        DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/icons/" . $this->MODULE_ID );
        DeleteDirFilesEx("/bitrix/images/sberbank.pokupay/");
        DeleteDirFilesEx($this->MODULE_ID);
    }

    function InstallDB($arParams = array()) {
        global $DB, $APPLICATION;
        $this->errors = false;
        // Database tables creation
        $this->errors = $DB->RunSqlBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/setup/db/" . strtolower($DB->type) . "/install.sql");
        
        if ($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }
        return true;
    }

    function UnInstallDB($arParams = array()) {
        // global $DB, $APPLICATION;
        // $this->errors = false;
        // if (!array_key_exists("save_tables", $arParams) || ($arParams["save_tables"] != "Y"))
        // {
        //     $this->errors = $DB->RunSqlBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/setup/db/" . strtolower($DB->type) . "/uninstall.sql");
        // }
        // if ($this->errors !== false)
        // {
        //     $APPLICATION->ThrowException(implode("<br>", $this->errors));
        //     return false;
        // }

        return true;
    }

    function DoInstall() {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
        COption::SetOptionInt($this->MODULE_ID, "delete", false);
        $this->InstallDB();

    }

    function DoUninstall() {
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
        COption::SetOptionInt($this->MODULE_ID, "delete", true);
        $this->UnInstallDB(array(
          "save_tables" => $_REQUEST["save_tables"],
        ));
        return true;        
    }
}

?>