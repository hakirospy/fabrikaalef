<? 
#####################################################
#        Company developer: IPOL
#        Developers: Karpov Sergey, Dmitry Pokrovskiy
#        Site: http://www.ipolh.com
#        E-mail: info@ipolh.com
#        Copyright (c) 2014- IPOL
#####################################################

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");	

CModule::IncludeModule("sale");
CModule::includeModule("ipol.kladr");
$module_id = "ipol.kladr"; 

$versionArr = explode(".", SM_VERSION);
$versionBx = (int) $versionArr[0];

// передаем информацию о версиях в js
echo "<script>var versionBx = '".$versionBx."'; var versionBxNewFunc = '".CKladr::$versionBxNewFunc."';</script>";

CJSCore::Init(array("jquery"));
include "js.php";
include "css.php"; 

//список настроек
$arAllOptions = array(
	"main" => Array(
		Array("FUCK", GetMessage("OPTNAME_FUCK"), '', Array("checkbox")),
		Array("FORADMIN", GetMessage("OPTNAME_FORADMIN"), '', Array("checkbox")),
		Array("JQUERY", GetMessage("OPTNAME_JQUERY"), '', Array("checkbox")),		
		Array("ADRCODE", GetMessage("OPTNAME_ADRCODE"), 'ADDRESS', Array("text")),
		Array("HIDELOCATION", GetMessage("OPTNAME_HIDELOCATION"), 'N', Array("checkbox")),
	),
	"beauty" => Array(
		Array("NOTSHOWFORM", GetMessage("OPTNAME_NOTSHOWFORM"), 'Y', Array("checkbox")),	
		Array("SHOWMAP", GetMessage("OPTNAME_SHOWMAP"), 'Y', Array("checkbox")),
		Array("NOLOADYANDEXAPI", GetMessage("OPTNAME_NOLOADYANDEXAPI"), 'N', Array("checkbox")),
		Array("YANDEXAPIKEY", GetMessage("OPTNAME_YANDEXAPIKEY"), '', Array("text")),
		Array("SHOWADDR", GetMessage("OPTNAME_SHOWADDR"), 'Y', Array("checkbox")),
		Array("DONTADDZIPTOADDR", GetMessage("OPTNAME_DONTADDZIPTOADDR"), '', Array("checkbox")),
		Array("DONTADDREGIONTOADDR", GetMessage("OPTNAME_DONTADDREGIONTOADDR"), '', Array("checkbox")),
		Array("MAKEFANCY", GetMessage("OPTNAME_MAKEFANCY"), '', Array("checkbox")),		
	),	
	"error" => Array(
		Array("ERRWRONGANSWER", GetMessage("OPTNAME_ERRWRONGANSWER"), '', Array("text")),
		Array("ERRWRONGANSWERDATE", GetMessage("OPTNAME_ERRWRONGANSWERDATE"), '', Array("text")),
	),
);

// Skipped deliveries - while user select some (usually pickup with PVZ map), we don't need KLADR form for it 
if ($versionBx >= 16)
{
	$skipDeliveries = array();	
	foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList(true) as $deliveryId => $deliveryFields)
	{
		$name = $deliveryFields["NAME"]." [";
		if (strlen($deliveryFields['CODE']) > 0 && !is_numeric($deliveryFields['CODE']))
			$name .= $deliveryFields['CODE']." - ";
		$name .= $deliveryId."]";		
		
		$sites = \Bitrix\Sale\Delivery\Restrictions\Manager::getSitesByServiceId($deliveryId);

		if(!empty($sites))
			$name .= " (".implode(", ", $sites).")";

		$skipDeliveries[$deliveryId] = $name;
	}	
	
	$arAllOptions["beauty"][] = Array("SKIPDELIVERIES", GetMessage("OPTNAME_SKIPDELIVERIES"), '', Array("multiselectbox", $skipDeliveries));
}

//список табов
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);

// Extra tabs and options
$extraTabs = array();

foreach (GetModuleEvents($module_id, "onTabsBuild", true) as $arEvent)
	ExecuteModuleEventEx($arEvent, Array(&$extraTabs));
	
$divId = count($aTabs);	
if (count($extraTabs))
{
	// protection for default properties blocks
	$restricted = array_keys($arAllOptions);
	
	foreach($extraTabs as $tab => $props)
	{
		$aTabs[] = array("DIV" => "edit".(++$divId), "TAB" => $tab, "TITLE" => $props['TITLE']);	
		if (count($props['BLOCKS']))
		{						
			foreach($props['BLOCKS'] as $key => $block)
			{
				if (!array_key_exists($key, $restricted))
					$arAllOptions[$key] = $block['OPTIONS'];	
			}							
		}			
	}		
}		
// --

//Save options
if ($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && check_bitrix_sessid())
{
	if (strlen($RestoreDefaults) > 0)
		COption::RemoveOption("ipol.towns");
	else {
		foreach ($arAllOptions as $aOptGroup)
			foreach ($aOptGroup as $option) {
				__AdmSettingsSaveOption($module_id, $option);
			}
	}
	
	if ($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
		echo '<script type="text/javascript">window.location="'.CUtil::addslashes($_REQUEST["back_url_settings"]).'";</script>';
}

function ShowParamsHTMLByArray($arParams)
{//пишет настройки
	foreach ($arParams as $Option)
		__AdmSettingsDrawRow($GLOBALS['module_id'], $Option);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?echo LANG?>">
	<?	
	$tabControl->Begin();
	$tabControl->BeginNextTab();	
	
	$errAnswerdate = intval(COption::GetOptionString("ipol.kladr", "ERRWRONGANSWERDATE"));
	$fail = false;
	if ($errAnswerdate > 0)
		if (mktime() - $errAnswerdate < 900) //если прошло 15 минут с ошибки
			$fail = true; //еще рано		
		
	if ($fail) {?> 
		<tr class="heading">
			<td colspan="2" valign="top" align="center"><?=GetMessage('ST_ERROR')?></td>
		</tr> 
		<tr>
			<td colspan="2" valign="top" align="center"><?=GetMessage('ERROR_0')?></td>
		</tr>
		<?ShowParamsHTMLByArray($arAllOptions["error"]);?>
	<?}?> 
	
	<tr class="heading">
		<td colspan="2" valign="top" align="center"><?=GetMessage('ST_HELP')?></td>
	</tr> 
	<tr>
		<td colspan="2" valign="top" align="left"><?=GetMessage('ST_HELP_TEXT')?></td>
	</tr> 	
	<tr class="heading">
		<td colspan="2" valign="top" align="center"><?=GetMessage('ST_MAINSET')?></td>
	</tr> 
	<?ShowParamsHTMLByArray($arAllOptions["main"]);?>
	
	<tr class="heading">
		<td colspan="2" valign="top" align="center"><?=GetMessage('ST_BEAUTY')?></td>
	</tr> 	
	<tr class="">
		<td colspan="2" valign="top" align="center"><?=GetMessage('CHANGE_CSS_TEXT');?></td>
	</tr> 
	<?ShowParamsHTMLByArray($arAllOptions["beauty"]);?>
	
	<?$popupHints = array(
		'pop-jQ'              => 'HINT_JQUERY',
		'adrcode'             => 'HINT_ADRCODE',
		'hidelocation'        => 'HINT_HIDELOCATION',
		'notshowform'         => 'HINT_NOTSHOWFORM',
		'noloadyandexapi'     => 'HINT_NOLOADYANDEXAPI',
		'yandexapikey'        => 'HINT_YANDEXAPIKEY',
		'dontaddziptoaddr'    => 'HINT_DONTADDZIPTOADDR',
		'dontaddregiontoaddr' => 'HINT_DONTADDREGIONTOADDR',
		'fancy'               => 'HINT_MAKEFANCY',		
	);
	
	if ($versionBx >= 16)
		$popupHints['skipdeliveries'] = 'HINT_SKIPDELIVERIES';
	
	foreach ($popupHints as $hintId => $hintMsg) {
	?>
		<div id="<?=$hintId?>" class="b-popup" style="display: none;">
			<div class="pop-text"><?=GetMessage($hintMsg)?></div>
			<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
		</div>		
	<?}		
	
	$tabControl->BeginNextTab();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	
	// Extra tabs and options	
	if (count($extraTabs))
	{
		foreach($extraTabs as $tab => $props)
		{
			$tabControl->BeginNextTab();						
			if (count($props['BLOCKS']))
			{
				foreach($props['BLOCKS'] as $key => $options)
				{
					?>
					<tr class="heading">
						<td colspan="2" valign="top" align="center"><?=$options['TITLE']?></td>
					</tr> 					
					<?ShowParamsHTMLByArray($arAllOptions[$key]);?>	
					<?
				}				
			}				
		}			
	}			
	// --
	
	$tabControl->Buttons();
	?>
	
	<div align="left">
		<input type="hidden" name="Update" value="Y">
		<input type="submit" <?if (!$USER->IsAdmin()) echo " disabled ";?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
	</div>
	<?$tabControl->End();?>
	<?=bitrix_sessid_post();?>
</form>