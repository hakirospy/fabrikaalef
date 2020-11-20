<?
	Ipolh\SDEK\Bitrix\Tools::placeWarningLabel(
		'<a href="/bitrix/js/'.$module_id.'/log.php" target="_blank">'.GetMessage('IPOLSDEK_LABEL_openLog').'</a>',
		(Ipolh\SDEK\Bitrix\Admin\Logger::getLog()) ? GetMessage('IPOLSDEK_LABEL_haslog') : GetMessage('IPOLSDEK_LABEL_nolog')
	);
?>

<?
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt")){
	$errorStr=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt");
	if(strlen($errorStr)>0){
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_FNDD_ERR_TITLE'),GetMessage('IPOLSDEK_FNDD_ERR_HEADER'));
	}
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt")){
	$updateStr=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt");
	if(strlen($updateStr)>0){
		Ipolh\SDEK\Bitrix\Tools::placeWarningLabel($updateStr,"<div class='IPOLSDEK_clz' onclick='IPOLSDEK_setups.base.clrUpdt()'></div>",300);
	}
}
?>

<?
foreach(array("debug_widget","startLogging","debug_fileMode","debug_calculation","debug_turnOffWidget") as $code)
	sdekOption::placeHint($code);
?>

<script>
    IPOLSDEK_setups.debug = {
        restorePVZ : function () {
            if(confirm('<?=GetMessage('IPOLSDEK_LBL_RESTOREPVZ')?>')){
                $('#SDEK_pvzRestore').attr('disabled','disabled');
                IPOLSDEK_setups.ajax({
                    data : {
                        isdek_action : 'restorePVZ'
                    },
                    dataType : 'JSON',
                    success  :function (data) {
                        if(data.SUCCESS){
                            alert('<?=GetMessage('IPOLSDEK_LBL_RESTORED')?>');
                        } else {
                            alert('<?=GetMessage('IPOLSDEK_LBL_UNRESTORED')?> '+data.ERROR);
                        }
                    }
                });
            }
        }
    };
</script>


<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_logging")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('LOGGING')?>
</td></tr>
<?ShowParamsHTMLByArray($arAllOptions["debug"]);?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_loggingEvents")?></td></tr>
<?ShowParamsHTMLByArray($arAllOptions["debug_events"]);?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_events")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('EVENTS')?>
</td></tr>
<?
$arEvents = array(
	'onCompabilityBefore' => getMessage('IPOLSDEK_LABEL_onCompabilityBefore'),
	'onCalculate' => getMessage('IPOLSDEK_LABEL_onCalculate'),
	'onTarifPriority' => getMessage('IPOLSDEK_LABEL_onTarifPriority'),
	'onBeforeDimensionsCount' => getMessage('IPOLSDEK_LABEL_onBeforeDimensionsCount'),
	'onCalculatePriceDelivery' => getMessage('IPOLSDEK_LABEL_onCalculatePriceDelivery'),
	'onBeforeShipment' => getMessage('IPOLSDEK_LABEL_onBeforeShipment'),
	'onGoodsToRequest' => getMessage('IPOLSDEK_LABEL_onGoodsToRequest'),
	'requestSended' => getMessage('IPOLSDEK_LABEL_requestSended'),
	'onParseAddress' => getMessage('IPOLSDEK_LABEL_onParseAddress'),
	'onNewStatus'    => getMessage('IPOLSDEK_LABEL_onNewStatus'),
	'onFormation' => getMessage('IPOLSDEK_LABEL_onFormation'),
	'onTabsBuild' => getMessage('IPOLSDEK_LABEL_onTabsBuild'),
	
);

foreach($arEvents as $code => $name){
	$arSubscribe = array();
	foreach(GetModuleEvents($module_id,$code,true) as $arEvent){
		$arSubscribe []= $arEvent['TO_NAME'];
	}
	if(!empty($arSubscribe)){
		?>
		<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=$name?></td></tr>
		<?
		foreach($arSubscribe as $path){?>
			<tr><td colspan='2'><?=$path?></td></tr>
		<?}
	}
}
	
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_defines")?></td></tr>
<tr><td style="color:#555;" colspan="2">
    <?sdekOption::placeFAQ('CONSTANTS')?>
</td></tr>

<?
    $arConstants = array(
        'IPOLSDEK_CACHE_TIME'    => GetMessage('IPOLSDEK_LABEL_CACHE_TIME'),
        'IPOLSDEK_NOCACHE'       => GetMessage('IPOLSDEK_LABEL_NOCACHE'),
        'IPOLSDEK_DOWNCOMPLECTS' => GetMessage('IPOLSDEK_LABEL_DOWNCOMPLECTS'),
        'IPOLSDEK_BASIC_URL'     => GetMessage('IPOLSDEK_LABEL_BASIC_URL'),
        'IPOLSDEK_CALCULATE_URL' => GetMessage('IPOLSDEK_LABEL_CALCULATE_URL'),
    );

    foreach($arConstants as $constant => $sign){
        if(defined($constant)){
            $constantSign = constant($constant);
            if(is_bool($constantSign)){
                $constantSign = ($constantSign) ? GetMessage('IPOLSDEK_LABEL_constantOn') : GetMessage('IPOLSDEK_LABEL_constantOff');
            }
            ?>
<tr>
    <td><?=$sign?></td><td><?=$constantSign?></td>
</tr>
            <?
        }
    }
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_pvzRestore")?></td></tr>
<tr><td style="color:#555;" colspan="2">
    <?sdekOption::placeFAQ('PVZRESTORE')?>
</td></tr>
<tr><td style="color:#555;" colspan="2"><br></td></tr>
<tr><td colspan="2"><input type="button" id="SDEK_pvzRestore" onclick="IPOLSDEK_setups.debug.restorePVZ()" value="<?=GetMessage('IPOLSDEK_LBL_RESTOREPVZBTN')?>"/></td></tr>

