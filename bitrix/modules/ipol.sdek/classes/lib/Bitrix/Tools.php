<?
namespace Ipolh\SDEK\Bitrix;

class Tools{
	private static $MODULE_ID  = IPOLH_SDEK;
	private static $MODULE_LBL = IPOLH_SDEK_LBL;

    // COMMON
    static function getMessage($code,$forseUTF=false)
    {
        $mess = GetMessage('IPOLSDEK_'.$code);
        if($forseUTF){
            $mess = \sdekHelper::zajsonit($mess);
        }
        return $mess;
    }

	static public function placeErrorLabel($content,$header=false)
	{?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap adm-info-message-red">
				<div class="adm-info-message">
					<?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
					<?=$content?>
					<div class="adm-info-message-icon"></div>
				</div>
			</div>
		</td></tr>
	<?}

	static public function placeWarningLabel($content,$header=false,$heghtLimit=false,$click=false)
	{?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap">
				<div class="adm-info-message" style='color: #000000'>
					<?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
					<?if($click){?><input type="button" <?=($click['id'] ? 'id="'.self::$MODULE_LBL.$click['id'].'"' : '')?> onclick='<?=$click['action']?>' value="<?=$click['name']?>"/><?}?>
						<div <?if($heghtLimit){?>style="max-height: <?=$heghtLimit?>px; overflow: auto;"<?}?>>
						<?=$content?>
					</div>
				</div>
			</div>
		</td></tr>
	<?}

    static public function isB24()
    {
        return (\COption::GetOptionString('sale','~IS_SALE_CRM_SITE_MASTER_FINISH','N') === 'Y');
    }

    static public function getB24URLs()
    {
        return array (
            'ORDER' => '/shop/orders/details/',
            'SHIPMENT' => '/shop/orders/shipment/details/',
        );
    }

    // OPTIONS
    static function placeFAQ($code){?>
        <a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=self::getMessage('FAQ_'.$code.'_TITLE')?></a>
        <div class="ipol_inst"><?=self::getMessage('FAQ_'.$code.'_DESCR')?></div>
    <?}

    static function placeHint($code){?>
        <div id="pop-<?=$code?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text"><?=self::getMessage("HELPER_".$code)?></div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?}

    /**
     * @param $code
     * makes da heading, FAQ und send command to establish included options
     */
    static function placeOptionBlock($code,$isHidden=false)
    {
        global $arAllOptions;
        ?>
        <tr class="heading"><td colspan="2" valign="top" align="center" <?=($isHidden) ? "class='".self::$MODULE_LBL."headerLink' onclick='".self::$MODULE_LBL."setups.getPage(\"main\").showHidden($(this))'" : ''?>><?=self::getMessage("HDR_".$code)?></td></tr>
        <?if(self::getMessage('FAQ_'.$code.'_TITLE')){?>
            <tr><td colspan="2"><?self::placeFAQ($code)?></td></tr>
        <?}
        /*if(Logger::getLogInfo($code)){
            self::placeWarningLabel(Logger::toOptions($code),self::getMessage("WARNING_".$code),150,array('name'=>Tools::getMessage('LBL_CLEAR'),'action'=>'IPONY_setups.getPage("main").clearLog("'.$code.'")','id'=>'clear'.$code));
        }*/
        if(array_key_exists($code,$arAllOptions)) {
            ShowParamsHTMLByArray($arAllOptions[$code], $isHidden);

            $collection = \Ipolh\SDEK\option::collection();
            foreach ($arAllOptions[$code] as $arOption){
                if(
                    array_key_exists($arOption[0],$collection) &&
                    $collection[$arOption[0]]['hasHint'] == 'Y'
                ){
                    self::placeHint($arOption[0]);
                }
            }
        }
    }

    /**
     * @param $name
     * @param $val
     * Draws tr-td. That's all. Bwahahahaha.
     */
    static function placeOptionRow($name, $val){
        if($name){?>
            <tr>
                <td width='50%' class='adm-detail-content-cell-l'><?=$name?></td>
                <td width='50%' class='adm-detail-content-cell-r'><?=$val?></td>
            </tr>
        <?}else{?>
            <tr><td colspan = '2' style='text-align: center'><?=$val?></td></tr>
        <?}?>
    <?}

    static function defaultOptionPath()
    {
        return "/bitrix/modules/".self::$MODULE_ID."/optionsInclude/";
    }
}
?>