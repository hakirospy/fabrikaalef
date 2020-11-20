<?
#################################################
#        Company developer: IPOL
#        Developer: Karpov Sergey, Pokrovski Dmitry
#        Site: http://www.ipol.com
#        E-mail: info@ipolh.com
#        Copyright (c) 2012-2016 IPOL
#################################################

IncludeModuleLangFile(__FILE__); 

if(class_exists("ipol_kladr")) 
    return;
	
Class ipol_kladr extends CModule
{
    var $MODULE_ID = "ipol.kladr";
    var $MODULE_NAME;
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";
	var $errors;

	function ipol_kladr()
	{ 
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("KLADR_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("KLADR_INSTALL_DESCRIPTION");
        
        $this->PARTNER_NAME = "Ipol";
        $this->PARTNER_URI = "http://ipolh.com";
	}
	
	function InstallDB()
	{
		return true;
	}


	function UnInstallDB()
	{
		return true;
	}
	
	function InstallEvents() {
		RegisterModuleDependences("sale","OnSaleComponentOrderOneStepPersonType", $this->MODULE_ID, "CKladr", "SetJS");
		RegisterModuleDependences("main", "OnPageStart",$this->MODULE_ID, "CKladr","SetLocation");
		
		RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "CKladr","OnSaleComponentOrderOneStepDeliveryHandler");
		RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "CKladr", "OnEndBufferContentHandler");
		return true;
	}
	
	function UnInstallEvents() {
		UnRegisterModuleDependences("sale","OnSaleComponentOrderOneStepPersonType", $this->MODULE_ID, "CKladr", "SetJS");
		UnRegisterModuleDependences("main", "OnPageStart",$this->MODULE_ID, "CKladr","SetLocation");
		
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "CKladr","OnSaleComponentOrderOneStepDeliveryHandler");
		UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "CKladr", "OnEndBufferContentHandler");
		return true;
	}

	function InstallFiles() {
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID, true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".$this->MODULE_ID, true, true);
		return true;
	}
	function UnInstallFiles()	{
		DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
		DeleteDirFilesEx("/bitrix/images/".$this->MODULE_ID);
		//
		return true;
	}
	
    function DoInstall()
    {
		global $DB, $APPLICATION, $step;
		$this->errors = false;
		
		$this->InstallDB();
		
		$this->InstallFiles();
		
		RegisterModule($this->MODULE_ID);
		$this->InstallEvents();
        $APPLICATION->IncludeAdminFile(GetMessage("KLADR_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
    }

    function DoUninstall()
    {
        global $DB, $APPLICATION, $step;
		$this->errors = false;
		 
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		
		UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("KLADR_DEL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/unstep1.php");
		
	
    }
}
?>
