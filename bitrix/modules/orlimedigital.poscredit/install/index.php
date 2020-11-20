<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Entity;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class orlimedigital_poscredit extends CModule {

    var $MODULE_ID = "orlimedigital.poscredit";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "N";
	var $PARTNER_NAME;
	var $PARTNER_URI;
	
	public function __construct() {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

		$this->exclusionAdminFiles = array(
			'..',
			'.',
			'menu.php',
			'operation_description.php',
			'task_description.php'
		);

        if(is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('POSCREDIT_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('POSCREDIT_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = "Orlime Digital";//Loc::getMessage('POSCREDIT_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'http://www.orlimedigital.ru';
    }

	function DoInstall() {
		$this->InstallFiles();		
		ModuleManager::registerModule($this->MODULE_ID);	
		
		CModule::IncludeModule("sale");
		
		$paySystemFind = CSalePaySystemAction::GetList(
			($arOrder = array()),
			($arFilter = array('%ACTION_FILE' => '%orlimedigital_poscredit')),
			($arGroupBy = false),
			($arNavStartParams = false),
			($arSelectFields = array())
		);
		$paySystemQuery = $paySystemFind->Fetch();
		
		if($paySystemQuery) {
			$paySystemId = $paySystemQuery['PAY_SYSTEM_ID'];
			$paySystemHandlerId = $paySystemQuery['ID'];
		} else {
			$arFields = array(
				'LID' => SITE_ID,
				'CURRENCY' => 'RUB',
				'NAME' => Loc::getMessage('POSCREDIT_SALE_SYSTEM_NAME'),
				'ACTIVE' => 'N',
				'SORT' => 200,
				'DESCRIPTION' => Loc::getMessage('POSCREDIT_SALE_SYSTEM_DESCRIPTION')
			);
			$paySystemId = CSalePaySystem::Add($arFields);
			
			$arFields = array(
				'PAY_SYSTEM_ID' => $paySystemId,
				'PERSON_TYPE_ID' => 1,
				'NAME' => Loc::getMessage('POSCREDIT_SALE_SYSTEM_HANDLER_NAME'),
				'ACTION_FILE' => '/bitrix/php_interface/include/sale_payment/orlimedigital_poscredit',
				'NEW_WINDOW' => 'N',
				'PARAMS' => '',
				'HAVE_PAYMENT' => 'Y',
				'HAVE_ACTION' => 'N',
				'HAVE_RESULT' => 'N',
				'HAVE_PREPAY' => 'N',
				'LOGOTIP' => CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/images/logo.png'),
			);
			$paySystemHandlerId = CSalePaySystemAction::Add($arFields);
		}

		Option::set(
			($module_id = $this->MODULE_ID),
			($option_id = 'PAY_SYSTEM_ID'),
			($value = $paySystemId)
		);
		Option::set(
			($module_id = $this->MODULE_ID),
			($option_id = 'PAY_SYSTEM_HANDLER_ID'),
			($value = $paySystemHandlerId)
		);
		
		return true;
	}

	function InstallFiles() {
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/payment', $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		if($dir = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/admin')) {
			while(false !== $item = readdir($dir)) {
				if(in_array($item, $this->exclusionAdminFiles))
					continue;
				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$this->MODULE_ID.'/admin/'.$item.'");?'.'>');
			}
			closedir($dir);
		}
		
		return true;
	}

	function DoUninstall() {
		CModule::IncludeModule("sale");
		
		$orders = \Bitrix\Sale\Internals\OrderTable::getList(
			($arOrder = array()),
			($arFilter = array()),
			($arGroupBy = false),
			($arNavStartParams = false),
			($arSelectFields = array())
		);

		$modulePaySystemId = Option::get($this->MODULE_ID, 'PAY_SYSTEM_ID');
		$deletePaySystem = true;
		while($order = $orders->Fetch()) {
			if($order['PAY_SYSTEM_ID'] == $modulePaySystemId) {
				$deletePaySystem = false;
				break;
			}
		}
		if($deletePaySystem) {
			CSalePaySystemAction::Delete(Option::get($this->MODULE_ID, 'PAY_SYSTEM_HANDLER_ID'));
			CSalePaySystem::Delete($modulePaySystemId);
		} else {
			CSalePaySystem::Update($modulePaySystemId, array('ACTIVE' => 'N') );
		}

		$this->UnInstallFiles();
		ModuleManager::unregisterModule($this->MODULE_ID);

		return true;
	}

	function UnInstallFiles($arParams = array()) {
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/sale_payment/orlimedigital_poscredit");
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT']."/bitrix/components/orlimedigital/poscreditbutton");

		if($dir = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/admin')) {
			while(false !== $item = readdir($dir)) {
				if(in_array($item, $this->exclusionAdminFiles))
					continue;
				\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item);
			}
			closedir($dir);
		}

		return true;
	}

}