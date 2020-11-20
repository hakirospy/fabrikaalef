<?
// Запрос местоположения для нового шаблока битрикса, основанного полностью на ajax
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::includeModule("ipol.kladr");
CModule::IncludeModule("sale");

$token = CKladr::getComercToken();
$delTypeInNameLoc = GetMessage("DEL_TYPE_IN_NAMELOC");

// типы местоположений
$contentTypes=array();
$contentTypesReq = \Bitrix\Sale\Location\TypeTable::getList(array(
	'select' => array('*', 'NAME_RU' => 'NAME.NAME'),
	'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)
));

while($c=$contentTypesReq->fetch()){
	
	foreach(GetMessage("TYPE_TOWN_SEARCH_BY_NAME") as $name){
		if(CKladr::toUpper($c["NAME_RU"]) == CKladr::toUpper($name)) {
			$contentTypes["city"][] = $c["ID"];
			break;
		}	
	}
	foreach(GetMessage("TYPE_TOWN_SEARCH_BY_CODE") as $code){
		if(CKladr::toUpper($c["CODE"]) == CKladr::toUpper($code)) {
			$contentTypes["city"][] = $c["ID"];
			break;
		}	
	}

	foreach(GetMessage("TYPE_REGION_SEARCH_BY_NAME") as $name){
		if(CKladr::toUpper($c["NAME_RU"]) == CKladr::toUpper($name)) {
			$contentTypes["region"][] = $c["ID"];
			break;
		}	
	}
	foreach(GetMessage("TYPE_REGION_SEARCH_BY_CODE") as $code){
		if(CKladr::toUpper($c["CODE"]) == CKladr::toUpper($code)) {
			$contentTypes["region"][] = $c["ID"];
			break;
		}	
	}	

}

if(!empty($contentTypes["city"]))
	$contentTypes["city"] = array_unique($contentTypes["city"]);
else {
	echo "error: Not Found Types"; exit();
}

if(!empty($contentTypes["region"]))
	$contentTypes["region"] = array_unique($contentTypes["region"]);

if($_GET["code"]) {
		
	$tree=array();
	if($itemTest=\Bitrix\Sale\Location\LocationTable::getByCode($_GET["code"], array(
	'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
	'select' => array('*','NAME_RU' => 'NAME.NAME')
	))->fetch()) {
					
		$location_tree=\Bitrix\Sale\Location\LocationTable::getPathToNodeByCode($_GET["code"], array(
			'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
			'select' => array('ID', 'CODE', 'TYPE_ID', 'NAME_RU' => 'NAME.NAME')
		));
		
		while($a=$location_tree->fetch()){ 
			
			foreach($delTypeInNameLoc as $r) {
				
				if(preg_match("/$r/", $a["NAME_RU"], $matches)) {
					
					$a["NAME_RU_QUERY"] = trim(str_replace($r, "", $a["NAME_RU"]));
					$a["TYPE_QUERY"] = $matches[0];
					break;
					
				}

			}
			
			$tree[] = $a;
		
		}
		
	}

	// location and contentType
	$location = array_pop($tree);
	$contentType=false;
	foreach($contentTypes as $type => $typesIdsBitrix){
		if(in_array($location["TYPE_ID"], $typesIdsBitrix)) {
			$contentType=$type;
			
			if($contentType != "city") {
				$n = explode(" ",$location["NAME_RU"]);
				$location["NAME_RU"] = $n[0];
				$location["NAME_RU_QUERY"] = $n[0];
			}
			
			break;
		}
	}
	if(!$contentType) {
		echo "error: Not Found Type"; exit();
	}

	// parents and if not rus
	$parents=$tree;

	$rus=false;
	foreach($parents as $key => $parent){
		if($parent["NAME_RU"] == GetMessage("RUSSIA_NAME")) {
			$rus=true;
			unset($parents[$key]);
			break;
		}	
	}
	if(!$rus) {
		echo "error: Not Russia"; exit();
	}

	// query
	$nameQuery = $location["NAME_RU_QUERY"] ? $location["NAME_RU_QUERY"] : $location["NAME_RU"];
	$query=urlencode((mb_detect_encoding($nameQuery)!=mb_internal_encoding() || SITE_CHARSET=='windows-1251') ? mb_convert_encoding($nameQuery, 'utf-8', 'cp1251') : $nameQuery);
	$timeOut=4;

	if(function_exists('curl_init') !== false){
		if( $curl = curl_init() )
		{//можно слать запросы

			$errAnswerdate = CKladr::GetErrorConnectDate();
			$fail=false;$code=false;
											
			$fail = CKladr::HaveErrors();
				
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
					
				if($code != 200)//или false если мы не отправляли запрос
				{
					
					if(strripos($out, 'token')=== false) { // выключаем, если это НЕ связано с токкеном
						if($code!==false){
							CKladr::SetErrorConnect(mktime(),$code);
						}
					}
								
					echo "error: " . $out;
					exit();
					
				}
				elseif($errAnswerdate){//если были ошибки то нужно их стереть
					CKladr::SetErrorConnect();
				}
				
				$a=(array)json_decode($out);
				$kladrid=false;
				$done=false;
				$countfindparentsmax=-1;
				
				foreach($a["result"] as &$object) {
					
					$object=(array)$object;
					
					if($contentType=='city') {
						
						if($object['name']!=CKladr::zajsonit($nameQuery)) continue;
						if(empty($object["parents"])){$kladrid=$object["id"];break;}
						
						$object["countfindparents"]=0;
						foreach($object["parents"] as $parent){
							
							foreach($parents as $parentBx) {
								
								$nameParentBx = CKladr::toUpper($parentBx["NAME_RU"]);
								$nameParent = CKladr::toUpper($parent->name);
								
								if(strpos($nameParentBx, $nameParent)!==false)
									$object["countfindparents"]++;
								
							}
							
						}
						
						if($object["countfindparents"]>$countfindparentsmax) {
							$countfindparentsmax=$object["countfindparents"];
							$kladrid=$object["id"];
						}
						
					} else {
						$kladrid=$object["id"];break;
					}	
					
				}
				
				if($kladrid) {
					
					foreach($a["result"] as $k=>$res){
						$p=(array)$res;
						if($p['id']==$kladrid) 
						{
							$townobj=json_encode($p, JSON_UNESCAPED_UNICODE);
							break;
						}
					}
								
					echo CKladr::zaDEjsonit($townobj);
					exit();
					
				} else {
					echo "error: Not Found Location";
					exit();
				}

			} else {
				echo "error: Last request will be faled";
				exit();
			}		
			
		} else {
			echo "error: Not Connect Kladr";
			exit();
		}
	}
	else{
		echo "error: Not Found Curl";
		exit();
	}

}

?>