<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'orlimedigital.poscredit');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl("tabControl", array(
	array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
		"ICON" => "ib_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ),
	array(
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
		"ICON" => "ib_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
    ),
));

if((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if(!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
    } else if($request->getPost('POSCREDIT_ACCESSID') && $request->getPost('PAY_SYSTEM_ID') && $request->getPost('PAY_SYSTEM_HANDLER_ID')) {
        Option::set(
            ADMIN_MODULE_NAME,
            "POSCREDIT_ACCESSID",
            $request->getPost('POSCREDIT_ACCESSID')
        );
		Option::set(
            ADMIN_MODULE_NAME,
            "POSCREDIT_TRADEID",
            $request->getPost('POSCREDIT_TRADEID')
        );		
		Option::set(
            ADMIN_MODULE_NAME,
            "PAY_SYSTEM_ID",
            $request->getPost('PAY_SYSTEM_ID')
        );
		Option::set(
            ADMIN_MODULE_NAME,
            "PAY_SYSTEM_HANDLER_ID",
            $request->getPost('PAY_SYSTEM_HANDLER_ID')
        );
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ));
    } else {
        CAdminMessage::showMessage(Loc::getMessage("REFERENCES_INVALID_VALUE"));
    }
}

$tabControl->begin();
?>

<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">

	<?php
	echo bitrix_sessid_post();
	$tabControl->BeginNextTab();
	?>

	<tr>
		<td width="40%">
            <label for="POSCREDIT_ACCESSID"><?=Loc::getMessage("POSCREDIT_ACCESSID") ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="15"
                   name="POSCREDIT_ACCESSID"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "POSCREDIT_ACCESSID");?>"
                   />
        </td>
	</tr>
	<tr>
		<td width="40%">
            <label for="POSCREDIT_TRADEID"><?=Loc::getMessage("POSCREDIT_TRADEID") ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="15"
                   name="POSCREDIT_TRADEID"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "POSCREDIT_TRADEID");?>"
                   />
        </td>
	</tr>	
	<tr>
		<td width="40%">
            <label for="PAY_SYSTEM_ID"><?=Loc::getMessage("POSCREDIT_PAY_SYSTEM_ID") ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="5"
                   name="PAY_SYSTEM_ID"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "PAY_SYSTEM_ID");?>"
                   />
        </td>
	</tr>
	<tr>
		<td width="40%">
            <label for="PAY_SYSTEM_HANDLER_ID"><?=Loc::getMessage("POSCREDIT_PAY_SYSTEM_HANDLER_ID") ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="5"
                   name="PAY_SYSTEM_HANDLER_ID"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "PAY_SYSTEM_HANDLER_ID");?>"
                   />
        </td>
	</tr>	

	<?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
           />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
           />
	<?php
	$tabControl->end();
	?>

</form>