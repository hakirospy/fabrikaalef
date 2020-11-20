<?
namespace Ipolh\SDEK;

IncludeModuleLangFile(__FILE__);

class pvzWidjetHandler /*extends abstractGeneral*/
{

    protected static $MODULE_LBL = IPOLH_SDEK_LBL;
    protected static $MODULE_ID  = IPOLH_SDEK;

	protected static $selDeliv = '';
	
	protected static $savingInput = 'chosenPVZ';
	protected static $postField   = 'sdek';

	public static function pickupLoader($arResult,$arUR){//подготавливает данные о доставке
		if(!\CDeliverySDEK::isActive()) return;

		\CDeliverySDEK::$orderWeight = ($arResult['ORDER_WEIGHT']) ? $arResult['ORDER_WEIGHT'] : \CDeliverySDEK::$orderWeight;
		\CDeliverySDEK::$orderPrice  = ($arResult['ORDER_PRICE'])  ? $arResult['ORDER_PRICE']  : \CDeliverySDEK::$orderPrice;

		$city = \CDeliverySDEK::getCity($arUR['DELIVERY_LOCATION'],true);
		\CDeliverySDEK::$cityId = $arUR['DELIVERY_LOCATION'];
		if($city){
			$city = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['NAME']);
			\CDeliverySDEK::$city = $city;
		}
		self::$selDeliv = $arUR['DELIVERY_ID'];
	}
	
	public static function loadComponent($arParams = array()){ // подключает компонент
		if(!is_array($arParams))
			$arParams = array();
		if(\CDeliverySDEK::isActive() && $_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]){
			if(option::get('noYmaps') == 'Y' || defined('BX_YMAP_SCRIPT_LOADED') || defined('IPOL_YMAPS_LOADED'))
				$arParams['NOMAPS'] = 'Y';
			elseif(!array_key_exists('NOMAPS',$arParams) || $arParams['NOMAPS'] != 'Y')
				define('IPOL_YMAPS_LOADED',true);
			if(option::get('vidjetSearch') === 'Y'){
                $arParams['SEARCH_ADDRESS'] = 'Y';
            }
			$componentName = option::get('widjetVersion');
			$GLOBALS['APPLICATION']->IncludeComponent('ipol:'.$componentName, "order", $arParams,false);
		}
	}

	public static function onBufferContent(&$content) {
		if(\CDeliverySDEK::$city && \CDeliverySDEK::isActive()){
			$arData = self::getCurrentOrderInfo();
			
			$noJson = self::no_json($content);
			if(($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"]) && $noJson){
				$content .= '<input type="hidden" id="sdek_city" name="sdek_city" value=\''.$arData['city'].'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_cityID" name="sdek_cityID" value=\''.$arData['cityId'].'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_dostav" name="sdek_dostav" value=\''.$arData['dostav'].'\' />';//вписываем выбранный вариант доставки
				$content .= '<input type="hidden" id="sdek_payer" name="sdek_payer" value=\''.$arData['payer'].'\' />';//вписываем плательщика
				$content .= '<input type="hidden" id="sdek_paysystem" name="sdek_paysystem" value=\''.$arData['paysystem'].'\' />';//вписываем платежную систему
				
				$content .= '<input type="hidden" id="'.self::getPostField().'" name="'.self::getPostField().'" value=\''.json_encode(\CDeliverySDEK::zajsonit($arData)).'\' />';//new widjet
			}elseif(($_REQUEST['soa-action'] == 'refreshOrderAjax' || $_REQUEST['action'] == 'refreshOrderAjax') && !$noJson)
				$content = substr($content,0,strlen($content)-1).',"'.self::getPostField().'":{"city":"'.\CDeliverySDEK::zajsonit($arData['city']).'","cityId":"'.$arData['cityId'].'","dostav":"'.$arData['dostav'].'","payer":"'.$arData['payer'].'","paysystem":"'.$arData['paysystem'].'"}}';
		}
	}

	public static function onAjaxAnswer(&$result){
		if(
			\CDeliverySDEK::$city && 
			\CDeliverySDEK::isActive() &&
			!array_key_exists('REDIRECT_URL',$result['order']) // $why = $because
		)
			$result['sdek'] = array(
				'city'   => \CDeliverySDEK::$city,
				'cityId' => \CDeliverySDEK::$cityId,
				'dostav' => self::$selDeliv
			);
	}
	
	public static function getMapsScript(){
		$path = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
		if($key = option::get('ymapsAPIKey')){
			$path .= '&apikey='.$key;
		}
		return $path;
	}
	
	public static function getCurrentOrderInfo(){
		return array(
			'city'      => \CDeliverySDEK::$city,
			'cityId'    => \CDeliverySDEK::$cityId,
			'dostav'    => self::$selDeliv,
			'payer'     => \CDeliverySDEK::$payerType,
			'paysystem' => \CDeliverySDEK::$paysystem
		);
	}
	
//	 public static function getPVZ($arParams = array()){
		// $arList = CDeliverySDEK::getListFile();
//		 $weight = option::get('weightD');
		// $arList['PVZ'] = CDeliverySDEK::weightPVZ($weight,$arList['PVZ']);
//	 }
	
	// SERVICE
	
	public static function getSavingInput(){
		return self::$MODULE_ID.self::$savingInput;
	}
	
	public static function getPostField(){
		return self::$postField;
	}
	
	protected static function no_json($wat){
		return is_null(json_decode(\CDeliverySDEK::zajsonit($wat),true));
	}
}
?>