<? 
IncludeModuleLangFile(__FILE__);
// Подключаем необходимые библиотеки
class CKladr
{
	static $MODULE_ID = "ipol.kladr";
	public static $townid;
	public static $NotRussia;
	public static $townobj;
	public static $contentType;
	public static $lastobject;
	public static $hidelocation;

	public static $towns=false;
	public static $padArray=false;
	public static $MacrArray=false;
	public static $thistown=false;
	public static $yatowns;
	public static $error=false;
	
	public static $versionBxNewFunc=16;
	
	function zajsonit($handle){// в UTF
		if(LANG_CHARSET !== 'UTF-8'){
			if(is_array($handle))
				foreach($handle as $key => $val){
					unset($handle[$key]);
					$key=self::zajsonit($key);
					$handle[$key]=self::zajsonit($val);
				}
			else
				$handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,LANG_CHARSET,'UTF-8');
		}
		return $handle;
	}
	function zaDEjsonit($handle){//из UTF
		if(LANG_CHARSET !== 'UTF-8'){
			if(is_array($handle))
				foreach($handle as $key => $val){
					unset($handle[$key]);
					$key=self::zaDEjsonit($key);
					$handle[$key]=self::zaDEjsonit($val);
				}
			else
				$handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,'UTF-8',LANG_CHARSET);
		}
		return $handle;
	}
		
	function PrintLog($somecontent, $rewrite=false,$err=false) {
		$filename = $_SERVER["DOCUMENT_ROOT"]."/KladrLog.txt";
		if($err) $filename = $_SERVER["DOCUMENT_ROOT"]."/KladrErrorLog.txt";
			if($rewrite) $rewrite = 'w'; else $rewrite = 'a';
			if(is_array($somecontent)) $somecontent = print_r($somecontent, true);
			if (!$handle = fopen($filename, $rewrite)) {
				 exit;
			}

			if (fwrite($handle, $somecontent) === FALSE) {
				exit;
			}

			fclose($handle);
	}
	
	function Error($err){
		self::PrintLog($err,false,true);
	}
	
	function toUpper($str,$lang=false){
		if(!$lang) $lang=LANG_CHARSET;
		$str = str_replace( //H8 ANSI
			array(
				GetMessage('IPOL_LANG_YO_S'),
				GetMessage('IPOL_LANG_CH_S'),
				GetMessage('IPOL_LANG_YA_S')
			),
			array(
				GetMessage('IPOL_LANG_YO_B'),
				GetMessage('IPOL_LANG_CH_B'),
				GetMessage('IPOL_LANG_YA_B')
			),
			$str
		);
		if(function_exists('mb_strtoupper'))
			return mb_strtoupper($str,$lang);
		else
			return strtoupper($str);
	}
	
	function getComercToken() {
		return "59c24e610a69de8d718b4582";
	}
	
	function SetJS(&$arResult, &$arUserResult, $arParams){		
		if(COption::GetOptionString(self::$MODULE_ID, "FUCK")=='Y') return;
				
		$errors = self::HaveErrors();
		if(!$errors)
			self::SetErrorConnect(); // сбросим		
		else
			return; // есть ошибки, не подключаем
		
		global $USER;
		$foradmin = COption::GetOptionString(self::$MODULE_ID, "FORADMIN");	
		if ($foradmin=='Y') { $foradmin=$USER->IsAdmin();} else {$foradmin=true;}//из настроек
		
		if($foradmin && $_REQUEST["PULL_UPDATE_STATE"] != 'Y' && $_REQUEST["PULL_AJAX_CALL"] != 'Y') // если не тестовый и не аякс
		{
			$jq = COption::GetOptionString(self::$MODULE_ID, "JQUERY");
			if($jq=='Y')  CJSCore::Init(array("jquery"));
			//задает параметры
			$KladrSettings=array(
				"kladripoladmin"=>false,// это администратор //
				"kladripoltoken"=> self::getComercToken(),// храним токен //
				"notShowForm"=> (COption::GetOptionString(self::$MODULE_ID, "NOTSHOWFORM")=='Y'),// не показывать форму при пустом локейшне //
				"hideLocation"=> (COption::GetOptionString(self::$MODULE_ID, "HIDELOCATION")=='Y'),// скрывать локейшн
				"code"=>COption::GetOptionString(self::$MODULE_ID, "ADRCODE"),
				"arNames"=>array(),// названиЯ форм длЯ города//
				"ShowMap"=>(COption::GetOptionString(self::$MODULE_ID, "SHOWMAP")=='Y'),
				"noloadyandexapi"=>(COption::GetOptionString(self::$MODULE_ID, "NOLOADYANDEXAPI")=='Y'),
				"YandexAPIkey"=>trim(COption::GetOptionString(self::$MODULE_ID, "YANDEXAPIKEY")),
				"ShowAddr"=>(COption::GetOptionString(self::$MODULE_ID, "SHOWADDR")=='Y'),
				"MakeFancy"=>(COption::GetOptionString(self::$MODULE_ID, "MAKEFANCY")=='Y'),
				"dontAddZipToAddr"=> (COption::GetOptionString(self::$MODULE_ID, "DONTADDZIPTOADDR")=='Y'), // don't add ZIP to address					
				"dontAddRegionToAddr"=> (COption::GetOptionString(self::$MODULE_ID, "DONTADDREGIONTOADDR")=='Y'), // don't add Region to address					
			);
			
			if ($skipDeliveries = COption::GetOptionString(self::$MODULE_ID, "SKIPDELIVERIES", ""))
			{
				$KladrSettings["skipDeliveries"] = explode(",", $skipDeliveries);
			}
			
			$KladrSettings["code"]=$KladrSettings["code"]?$KladrSettings["code"]:"ADDRESS";
			if(strpos($KladrSettings["code"], ',')!==false){
				$KladrSettings["code"]=explode(',',$KladrSettings["code"]);
			}
			
			if($USER->IsAdmin()) $KladrSettings["kladripoladmin"]=true;
				
				//получить заменяемое поле адреса
				CModule::IncludeModule("sale");
				$db_props = CSaleOrderProps::GetList(array("SORT" => "ASC"),array("CODE"=>$KladrSettings["code"],),false,false,array("ID")); 
				while ($props = $db_props->Fetch()) 
				{
					$KladrSettings["arNames"][]="ORDER_PROP_".$props["ID"];
				}
				if(!is_array($KladrSettings["arNames"]) || empty($KladrSettings["arNames"]))
				{
					self::Error("TEXTAREA NOT FOUND");
					return;
				}

				$versionArr = explode(".", SM_VERSION);
				$versionBx = (int) $versionArr[0];
				
				if($KladrSettings["hideLocation"] && $versionBx >= self::$versionBxNewFunc) {
					// есть ли в системе страны помимо России, если есть, то предусматриваем опцию - "не Россия", с возможностью переключиться в стандартный режим
					$country_not_rus_codes = array();
					$country_rus_id = "";
					$country_rus_code = "";
					$parameters = array();
					$parameters['filter']['NAME.LANGUAGE_ID'] = LANGUAGE_ID;
					$parameters['filter']['DEPTH_LEVEL'] = 1;
					$parameters['select'] = array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME');
					$res = Bitrix\Sale\Location\LocationTable::getList( $parameters );
					while($item = $res->fetch())
					{
						if($item["NAME_RU"]!=GetMessage("RUSSIA_NAME")) {
							$country_not_rus_codes[] = $item["CODE"];
						} else {
							$country_rus_id = $item["ID"];
							$country_rus_code = $item["CODE"];
						}
					}
					if(!empty($country_not_rus_codes))
						$KladrSettings["locations_not_rus"] = true;
					if(!empty($country_rus_id))
						$KladrSettings["country_rus_id"] = $country_rus_id;
					if(!empty($country_rus_code))
						$KladrSettings["country_rus_code"] = $country_rus_code;
				}
				
				// custom handlers for JS
				$KladrSettings["handlers"] = array('onMapCreated' => '');
				
				foreach (GetModuleEvents(self::$MODULE_ID, "onJSHandlersSet", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, Array(&$KladrSettings["handlers"]));

				global $APPLICATION;
				//скрипты нужные
				$APPLICATION->AddHeadString('<script>var KladrSettings;KladrSettings = '.json_encode($KladrSettings).';</script>',true);
				
				$APPLICATION->AddHeadString('<script src="/bitrix/js/'.self::$MODULE_ID.'/jquery.fias.min.js" type="text/javascript"></script>',true);
				//css
				if(file_exists($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH.'/css/kladr.css'))
				{
					$APPLICATION->AddHeadString('<link rel="stylesheet" href="'.SITE_TEMPLATE_PATH.'/css/kladr.css'.'">',true);
				}
				else
					$APPLICATION->AddHeadString('<link href="/bitrix/js/'.self::$MODULE_ID.'/kladr.css" rel="stylesheet">',true);
								
				if($KladrSettings["ShowMap"] && !$KladrSettings["noloadyandexapi"] && !defined('BX_YMAP_SCRIPT_LOADED') && !defined('IPOL_YMAPS_LOADED')) {
					if (strlen($KladrSettings["YandexAPIkey"]) > 1)
						$APPLICATION->AddHeadString('<script src="//api-maps.yandex.ru/2.1/?load=package.standard&mode=release&lang=ru-RU&apikey='.$KladrSettings["YandexAPIkey"].'" type="text/javascript"></script>',true);
					else
						$APPLICATION->AddHeadString('<script src="//api-maps.yandex.ru/2.1/?load=package.standard&mode=release&lang=ru-RU" type="text/javascript"></script>',true);
					
					define("IPOL_YMAPS_LOADED", "Y"); // говорим, что карта загружена
				}
						
				$APPLICATION->AddHeadScript('/bitrix/js/'.self::$MODULE_ID.'/ipolkladr.js');
				
				CJSCore::Init(array('ipolkladr'));
		}
	}
	
	function retRegion($region){//$region - область в кодировке сайта 
		$arChange=GetMessage('CHANGE');
		foreach($arChange as $key => $value) {$k = self::toUpper($key); unset($arChange[$key]); $arChange[$k] = $value;}
		if(in_array(self::toUpper($region),array_keys($arChange))) $region=$arChange[self::toUpper($region)]; 
		return $region;
	}
	
	function retType($type){//$type - приписка для имени города,села,деревни в кодировке сайта 
		$arChange=GetMessage('CHANGE_TYPES_FOR_NAMES');
		foreach($arChange as $key => $value) {$k = self::toUpper($key); unset($arChange[$key]); $arChange[$k] = $value;}
		if(in_array(self::toUpper($type),array_keys($arChange))) $type=$arChange[self::toUpper($type)];
		return $type;
	}

	function SetLocation(){//только меняет реквест, чтобы поставить город

		if($_REQUEST["ipolkladrnewregion"]) $region=self::retRegion($_REQUEST["ipolkladrnewregion"]);			

		if($_REQUEST["ipolkladrlocation"]){
			if($_REQUEST["ipolkladrnewcity"])
			{
				CModule::IncludeModule("sale");
				//пытаемся определить id
				$arFilter=array("LID" => LANGUAGE_ID,"CITY_NAME"=>$_REQUEST["ipolkladrnewcity"]);
				if($region) $arFilter["REGION_NAME"]=$region;
				$db_vars = CSaleLocation::GetList(array(),$arFilter,false,false,array());
				if ($vars = $db_vars->Fetch()){
					$locationid=$vars["ID"];
				}
				if(!$locationid){
					//пытаемся определить область
					if($_REQUEST["ipolkladrnewregion"])
						//сверить
						$db_vars = CSaleLocation::GetList(
							array(
									"SORT" => "ASC",
									"COUNTRY_NAME_LANG" => "ASC",
									"CITY_NAME_LANG" => "ASC"
								),
							// array("LID" => LANGUAGE_ID,"CITY_NAME"=>$_REQUEST["newcity"]),
							array("LID" => LANGUAGE_ID,"REGION_NAME"=>$region),
							false,
							false,
							array()
						);
						if ($vars = $db_vars->Fetch()){
							$locationid=$vars["ID"];	
						}
				}
				
				//ставим id в местоположение
				if($locationid){
					// пишем id города
					$_REQUEST[$_REQUEST["ipolkladrlocation"]]=$locationid;
					$_POST[$_REQUEST["ipolkladrlocation"]]=$locationid;
					$_GET[$_REQUEST["ipolkladrlocation"]]=$locationid;
				} 
			}	
		}			
	}

	function OnSaleComponentOrderOneStepDeliveryHandler(&$arResult, &$arUserResult, $arParams)
	{//когда загрузились свойства доставки
		if((COption::GetOptionString(self::$MODULE_ID, "FUCK")=='Y')) return;
		
			$city=array();
			$token = self::getComercToken();//MOD
			// self::SetErrorConnect();
			//формируем массив параметров заказа в котором будем искать город
			if(is_array($arResult["ORDER_PROP"]["USER_PROPS_Y"])) 
			{
				$arPr=$arResult["ORDER_PROP"]["USER_PROPS_Y"];
				if(is_array($arResult["ORDER_PROP"]["USER_PROPS_N"]))
					$arPr=array_merge($arResult["ORDER_PROP"]["USER_PROPS_Y"],$arResult["ORDER_PROP"]["USER_PROPS_N"]);
			}
			elseif(is_array($arResult["ORDER_PROP"]["USER_PROPS_N"])) {
				$arPr=$arResult["ORDER_PROP"]["USER_PROPS_N"];
			}

			if(is_array($arPr))
			{	
				//пробуем определить выбранное местоположение, если есть свойства (старый стиль)
				foreach($arPr as $arropname=>$prop)
				{
					if($prop["TYPE"] == 'LOCATION')
					{
						foreach($prop["VARIANTS"] as $town){
							if($town["ID"] == $prop["VALUE"]) $city=$town;
							if($town["SELECTED"]== 'Y') $city=$town;
						}
					}
				}
			}
			else{//иначе берем из $arUserResult
				$db_props = CSaleOrderProps::GetList(array("SORT" => "ASC"),array("IS_LOCATION"=>'Y',"PERSON_TYPE_ID"=>$arUserResult["PERSON_TYPE_ID"]),false,false,array("ID","IS_LOCATION","DEFAULT_VALUE"));
				if ($props = $db_props->Fetch()) 
				{
					if($arUserResult["ORDER_PROP"][$props["ID"]])
						$city=array("ID"=>$arUserResult["ORDER_PROP"][$props["ID"]]);
					else
						$city=array("ID"=>$props["DEFAULT_VALUE"]);
				}
			}
			
			//пробуем добыть название города и регионов-родителей
			$regionname=false;
			$countryname=false;
			$townname=false;

			if($city["ID"]){//если был выбран город

				$vars = self::getCityNameByID($city["ID"]);

				if (is_array($vars)){
					$city=$vars;
					$regionname=self::zajsonit($vars["REGION_NAME"]);
					$countryname=self::zajsonit($vars["COUNTRY_NAME"]);
					$townname=self::zajsonit($vars["CITY_NAME"]);
				}
			}
						
			if(is_array($city) && !empty($city)){
				if($city["COUNTRY_NAME"] != GetMessage("RUSSIA_NAME")) 
					CKladr::$NotRussia=true;
				else
				{

					CKladr::$NotRussia=false;
					//пытаемсЯ понЯть, что ввели, проверЯем, что у битрикса в местоположении было заполнено
					if($townname){
					//город
						$contentType='city';
						$townname=trim(str_replace(explode(',',GetMessage("CHANGEINTOWN")), "", $townname));
						$query=urlencode($townname);
					}
					elseif($regionname){
					//область 
						$contentType='region';
						$r = explode(" ",$regionname);
						$query=urlencode($r[0]);
					}
					else{
					//страна? но со странами КЉладр не работает
					//предположительно длЯ местоположений 2.0 при выборе района
						$regionname=self::zajsonit($city["REGION_NAME"]);
						$contentType='region';
						$r = explode(" ",$regionname);
						$query=urlencode($r[0]);
					}
			 
					//пробуем добыть ид города в системе кладр
					// $query='';
					$timeOut=4;
					if(function_exists('curl_init') !== false){
						if( $curl = curl_init() )
						{//можно слать запросы
							$errAnswerdate = self::GetErrorConnectDate();
							$fail=false;$code=false;
							
							$fail = self::HaveErrors();
								
							if(!$fail){//если не было ошибок
								
								if(!$token)
									curl_setopt($curl, CURLOPT_URL, 'http://kladr-api.ru/api.php?query='.$query.'&contentType='.$contentType.'&withParent=1');
								else
									curl_setopt($curl, CURLOPT_URL, 'http://kladr-api.com/api.php?query='.$query.'&contentType='.$contentType.'&withParent=1&token='.$token);
								
								curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($curl, CURLOPT_TIMEOUT,$timeOut);
												
								$out = curl_exec($curl);
								$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
								curl_close($curl);
							}

							if($code != 200)//или false если мы не отправляли запрос
							{
								// какая-то лажа
								self::Error("cUrl error. Wrong answer ".$code);
								
								if(strripos($out, 'token')=== false) { // выключаем, если это НЕ связано с токкеном
									if($code!==false){
										self::SetErrorConnect(mktime(),$code);
									}
								}
								
								CKladr::$error=true;
								//уронить кладр.
								return;
								
							}
							elseif($errAnswerdate){//если были ошибки то нужно их стереть
								self::SetErrorConnect();
							}
							
							$a=(array)json_decode($out);
							$kladrid='';//здесь будет город занесен.
							$done=false;

							if(is_array($a["result"]))
								foreach($a["result"] as $resulttown)
								{
									$resTown=(array)$resulttown;//объект в массив. ђезультат всегда в ютф

									if($contentType=='city')
									{//у города надо проверить область, а у областей нет
										//1) проверка на имЯ
										// if($resTown['name']!=self::zajsonit($city["CITY_NAME"])) continue;//имЯ не подошло
										if($resTown['name']!=self::zajsonit($townname)) continue;//имЯ не подошло

										//2)проверка на тип
										// if($resTown['type']!=self::zajsonit(GetMessage("TYPE_TOWN"))) continue;//имЯ не подошло
											
										//3)проверка родителей
										if(!empty($resTown["parents"]))
										{
											foreach($resTown["parents"] as $parent){
												$parent=(array)$parent;
												
												//названиЯ областий в длинном и коротком виде
												if(strpos($regionname, ' ')!==false)
												{
													$par[$parent["contentType"]."_short"]=self::toUpper($parent["name"].' '.$parent["typeShort"],'UTF-8');
													$par[$parent["contentType"]."_full"]=self::toUpper($parent["name"].' '.$parent["type"],'UTF-8');
													$par[$parent["contentType"]."_full2"]=self::toUpper($parent["type"].' '.$parent["name"],'UTF-8');
												}
												else{
													$par[$parent["contentType"]."_short"]=self::toUpper($parent["name"],'UTF-8');
													$par[$parent["contentType"]."_full"]=self::toUpper($parent["name"],'UTF-8');
													$par[$parent["contentType"]."_full2"]=self::toUpper($parent["type"],'UTF-8');
													
												}
												
												$regionname=self::toUpper($regionname,'UTF-8');
												
												$execp_city_id=array_search($regionname,GetMessage("NAME_BAD"));
												if($execp_city_id!==false){
													$ar_new_towns=GetMessage("NAME_KLADR");

													$regionname=$ar_new_towns[$execp_city_id];
												}
																									
												if($par[$parent["contentType"]."_short"]==$regionname || 
													$par[$parent["contentType"]."_full"]==$regionname || 
													$par[$parent["contentType"]."_full2"]==$regionname) 
												{
													$kladrid=$resTown['id'];	
													$done=true;//хватит перебирать
												} 
												
											}//от foreeach
										}	
										else
										{//родителей нет 
											$emptyid=$resTown["id"];//пустого города
										}
									}		
									else
									{//регион. добыть регион исходЯ из того, что родителей у региона быть не может, и вообще он такой один
										$kladrid=$resTown["id"];break;
									}
									if($done) break;//если город нашли, то валим
									
								}//endforeach	
								if(!$kladrid && $emptyid) $kladrid=$emptyid;// если области не совпали, то брать без области если есть
						
								// если местоположение не подошло по критериям, но в результате есть записи, то скорее всего первый найденный - правильный
								// в частности это касается городов, у которых родитель является городом, либо просто написание другое
								if(!$kladrid && is_array($a["result"])) {
									$kladrid = $a["result"][0]->id;
								}
						
						}//если запрос удалсЯ
						else{
							self::Error("cUrl error. Request lost.");
						}
					}
					else{//
						self::Error("cUrl error. cUrl not found.");
					}
					
					//запомнить в модуль
					if($kladrid)
					{
						foreach($a["result"] as $k=>$resulttown){
							
							$p=(array)$resulttown;
							if($p['id']==$kladrid) 
							{
								$townobj=json_encode($p);
							}
						}				
						self::$townid=$kladrid;
						self::$townobj=$townobj;
						self::$contentType=$contentType;
					}
					else
					{//если города не найден в кладре, то не убивать // что??
						self::Error("kladrid not found");
					}
				}
			}
			else{// $city пуст . Город не найден
				self::Error("Town not found");
				if((COption::GetOptionString(self::$MODULE_ID, "NOTSHOWFORM")=='Y')) CKladr::$NotRussia=true;
			}
			
		$obj=self::GetKladrSetText('obj');
		if(!$obj) $obj='arg:{}';
		
		echo '<script>';
		echo '$(document).ready(function(){
				KladrJsObj.FormKladr({'.$obj.',"ajax":false});
				KladrJsObj.checkErrors();
			});';
		echo '</script>';
	}
	
	function OnEndBufferContentHandler(&$content)
	{
		if((COption::GetOptionString(self::$MODULE_ID, "FUCK")=='Y')) return;
		if ((defined("ADMIN_SECTION") && ADMIN_SECTION == true) || strpos($_SERVER['PHP_SELF'], "/bitrix/admin") === true) return false;
		
		if ($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"])
		{
		
			$noJson=self::no_json($content);
			$error= CKladr::$error;
			if($error)$NotRussia=true;//кривой способ шатнуть кладр
			if($noJson){
				CKladr::$townid=false;
				$text=self::GetKladrSetText('text');

				if($text)
					$content.= $text;
			}elseif($_REQUEST['action'] == 'refreshOrderAjax' && !$noJson){
				$text=self::GetKladrSetText('obj');
				
				if($text)
					$content = substr($content,0,strlen($content)-1).','.$text.'}';
			}
		
		}
		
	}
	
	function GetKladrSetText($type){
		$kltobl= CKladr::$townobj;
		$NotRussia= CKladr::$NotRussia;
		
		$contentType= CKladr::$contentType;
		$kladrid= CKladr::$townid;

		$text='';
		if($kltobl || $NotRussia){
			if($type=='text'){
				if($NotRussia)
					$text .='<input type="hidden" value="'.$NotRussia.'" class="NotRussia" name="NotRussia"/>';
				if($kltobl)
					$text .='<div style="display:none;" class="kltobl" >'.$kltobl.'</div>';
			}
			elseif($type=='obj'){
				$text='"kladr":{"kltobl":"'.addslashes($kltobl).'","NotRussia":"'.$NotRussia.'"}';	
			}
		}

		return $text;		
	} 
	
	function ParseAddr($adr){
		$ans = array();
		$containment = explode(",",$adr);
		$numCon=count($containment);
		if(is_numeric(trim($containment[0]))) $start = 2;
		else $start = 1;		
		if($numCon-$start == 3){
			$ans['town'] = trim($containment[$start]);
			$start++;
		}
		if($containment[$start]){$ans['line'] = trim($containment[$start]);}
		if($containment[($start+1)]){ $containment[($start+1)] = trim($containment[($start+1)]); $ans['house'] = trim(substr($containment[($start+1)],strpos($containment[($start+1)]," ")));}
		if($containment[($start+2)]){ $containment[($start+2)] = trim($containment[($start+2)]); $ans['flat']  = trim(substr($containment[($start+2)],strpos($containment[($start+2)]," ")));}
		
		return $ans;
	}
	
	function no_json($wat){
		return is_null(json_decode(self::zajsonit($wat),true));
	}
	
	function getCityNameByID($locationID)
	{
		if(method_exists("CSaleLocation","isLocationProMigrated") && CSaleLocation::isLocationProMigrated())
		{//если Местположения 2.0
			
			//получаем id по коду, если это код
			if (strlen($locationID) > 8)
				$cityID = CSaleLocation::getLocationIDbyCODE($locationID);
			else
				$cityID = $locationID;
			
			//получаем всю цепочку
			$res = \Bitrix\Sale\Location\LocationTable::getList(array(
				'filter' => array(
					'=ID' => $cityID, 
					'=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
					'=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
					'!PARENTS.TYPE.CODE' => 'COUNTRY_DISTRICT',
					'I_TYPE_CODE'=>array("COUNTRY","REGION","CITY"),
				),
				'select' => array(
					'I_ID' => 'PARENTS.ID',
					'I_NAME_RU' => 'PARENTS.NAME.NAME',
					'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
					'I_TYPE_NAME_RU' => 'PARENTS.TYPE.NAME.NAME'
				),
				'order' => array(
					'PARENTS.DEPTH_LEVEL' => 'asc'
				)
			));
			
			while($item = $res->fetch())
			{	
				$arCity[$item["I_TYPE_CODE"]."_NAME"]=$item["I_NAME_RU"];//если города было 2, то перезапишет на последний
			}
			if(!array_key_exists ("CITY_NAME",$arCity))	
				$arCity["CITY_NAME"]=$arCity["REGION_NAME"];
			
		}
		else
			$arCity = CSaleLocation::GetByID($locationID);
	  
	    return $arCity;
	}
	
	public function SetErrorConnect($unix=false,$code=false) {
		
		COption::SetOptionString(self::$MODULE_ID,"ERRWRONGANSWERDATE",$unix);
		COption::SetOptionString(self::$MODULE_ID,"ERRWRONGANSWER",$code);
		
	}
	
	public function GetErrorConnectDate() {
		
		return intval(COption::GetOptionString(self::$MODULE_ID, "ERRWRONGANSWERDATE"));
		
	}
	
	public function HaveErrors() {
		
		// были ли ошибки, и если были прошло ли 15 минут ?
		$errAnswerdate = self::GetErrorConnectDate();
		if($errAnswerdate>0) {
			if(mktime()-$errAnswerdate<900){//если прошло 15 минут с ошибки
				$errors=true;//еще рано
			}
			else
			{
				$errors=false;
			}
		} else {//ошибок не было
			$errors=false;
		}
		
		return $errors;
	
	}

	// достаем локейшн с битрикса
	function getBitrixLocationCodeByName(){
		
		if($_REQUEST["ipolkladrlocation"]){
			if($_REQUEST["ipolkladrnewcity"])
			{
				
				$ipolkladrnewcity=$_REQUEST["ipolkladrnewcity"];
				$ipolkladrnewregion=$_REQUEST["ipolkladrnewregion"];
				$ipolkladrnewtype=$_REQUEST["ipolkladrnewtype"];
				$country_rus_id=$_REQUEST["country_rus_id"];
				$country_rus_code=$_REQUEST["country_rus_code"];

				CModule::IncludeModule("sale");
				
				// параметры для транслитерации
				$translitParams = array("replace_space"=>"-","replace_other"=>"-","change_case"=>false);

				// если вместе с городом/деревней/селом пришло дерево родителей (регионов)
				if($ipolkladrnewregion) {
					
					$regions = explode(",", $ipolkladrnewregion);
					foreach($regions as &$value)
						$value=self::retRegion($value);

				}

				// достаем все типы местоположений
				$typesCode=array();
				
				$res = \Bitrix\Sale\Location\TypeTable::getList(array(
					'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME'),
					'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)
				));
								
				while($item = $res->fetch())
				{
					
					$typesCode[$item["CODE"]] = $item["ID"]; // все существующие типы
					
				}

				// регион по умолчанию - Россия
				// если не известны id и код России
				if(empty($country_rus_id) || empty($country_rus_code)) {
					
					// достаем id местоположения России, чтобы определить корневой регион
					$filter=array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=NAME.NAME' => GetMessage("RUSSIA_NAME"));
					
					$res = \Bitrix\Sale\Location\LocationTable::getList(array(
						'filter' => $filter,
						'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
					));

					if($item = $res->fetch()){
						$regionid=$item["ID"]; // по умолчанию корневой регион - Россия
						$regioncode=$item["CODE"];
					}
				
				// экономия запроса, если данные пришли в параметрах
				} else {
					
					$regionid=$country_rus_id;
					$regioncode=$country_rus_code;
					
				}

				// далее необходимо найти все название с типом "город/село/деревня"
				$type='';
				if($ipolkladrnewtype) $type=self::retType($ipolkladrnewtype);
				if(!empty($type))
					$ipolkladrnewcity = $ipolkladrnewcity . " " . $type;

				$filter=array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=NAME.NAME' => $ipolkladrnewcity);
				
				$res = \Bitrix\Sale\Location\LocationTable::getList(array(
					'filter' => $filter,
					'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
				));

				// вместе пришли регионы
				if($regions) {
					
					while($item = $res->fetch())
					{
						
						// в каждой итерации ставим найденную локацию, пока не найдем нужную
						$locationid = $item["ID"];
						$locationcode = $item["CODE"];
						$locationname = $item["NAME_RU"];

						$tree = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode($locationcode, array(
							'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
							'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
						));
					
						$parents=array(); // обнуляем массив родителей
						while($i = $tree->fetch())
						{
							if($item["ID"] == $i["ID"]) continue; // пропускаем сам элемент
							$parents[] = $i["NAME_RU"];
						}

						// проверяем максимум двойную вложенность дерева
						// думаю что в подрегионе двух одинаковых наименований нет
						if(in_array($regions[0], $parents)) { // нашелся регион 
							$find = true; // предварительно значит нашли местоположение
							if(!empty($regions[1])) // если есть саб. регион 
								if(!in_array($regions[1], $parents)) // но его нет в дереве
									$find = false; // отменяем находку
						}

						// выходим из цикла, а $locationid остается текущим
						if($find)
							break;
						else
							$locationid = false;
						
					}

					// так и не нашли города/села/деревни по имени, но есть регионы
					// пытаемся выстроить дерево регионов
					if(!$locationid) {

						// ищем регионы заполняем недостающие в битриксе
						$insertRegions=$regions;
						foreach($regions as $key => $region) {

							$filter=array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=NAME.NAME' => $region);

							$res = \Bitrix\Sale\Location\LocationTable::getList(array( // 2,00 с
								'filter' => $filter,
								'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
							));	

							// если нашли
							while($item = $res->fetch()){
								
								$findRegion=false;
								// также найденый регион должен быть в потомках корневого
								if($regioncode) {
									// косяк, у вставленных элементов не видит целое дерево
									$tree = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode($item["CODE"], array( 
										'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
										'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
									));
									// добавлено $country_rus_code
									$parentsRegion = array($country_rus_code);
									while($i=$tree->fetch()){
										$parentsRegion[] = $i["CODE"];
									}

									if(in_array($regioncode, $parentsRegion)) {
										$regionid=$item["ID"]; // найден регион с таким именем
										$regioncode=$item["CODE"];
										unset($insertRegions[$key]);
										$findRegion=true;
										break;
									}
									
								}

							}
							
							if(!$findRegion)
								break;

						}

					}
				
				} else {
					
					if($item = $res->fetch()) {
						
						$locationid = $item["ID"]; // первый найденный
						$locationcode = $item["CODE"];
						$locationname = $item["NAME_RU"];

					}

				}
				
				$isRegion = 0;
				//ставим id в местоположение
				if($locationid){
					// пишем id города
					$_REQUEST[$_REQUEST["ipolkladrlocation"]]=$locationid;
					$_POST[$_REQUEST["ipolkladrlocation"]]=$locationid;
					$_GET[$_REQUEST["ipolkladrlocation"]]=$locationid;
				} else {
					$locationid = $regionid;
					$isRegion = 1;
				}
				
				if(!$locationcode && $locationid) {
					
					$filter=array('ID' => $locationid);
					$res = \Bitrix\Sale\Location\LocationTable::getList(array(
						'filter' => $filter,
						'select' => array('ID', 'CODE', 'NAME_RU' => 'NAME.NAME')
					));
					$item = $res->fetch();
					$locationcode = $item["CODE"];
					$locationname = $item["NAME_RU"];
					
				}
				
				return array("code" => $locationcode,"name" => $locationname,"isRegion" => $isRegion); // если ничего не вставилось, то возвращаем найденный код

			}
		}			
	}
	
}//от класа 
