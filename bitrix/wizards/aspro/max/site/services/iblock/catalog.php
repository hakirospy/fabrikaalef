<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";

$iblockShortCODE = "catalog";
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/".$iblockShortCODE.".xml";
$iblockTYPE = "aspro_max_catalog";
$iblockXMLID = "aspro_max_".$iblockShortCODE."_".WIZARD_SITE_ID;
$iblockCODE = "aspro_max_".$iblockShortCODE;
$iblockID = false;

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockXMLID, "TYPE" => $iblockTYPE));
if ($arIBlock = $rsIBlock->Fetch()) {
	$iblockID = $arIBlock["ID"];
	if (WIZARD_INSTALL_DEMO_DATA) {
		// delete if already exist & need install demo
		CIBlock::Delete($arIBlock["ID"]);
		$iblockID = false;
	}
}

if(WIZARD_INSTALL_DEMO_DATA){
	if(!$iblockID){
		// add new iblock
		$permissions = array("1" => "X", "2" => "R");
		$dbGroup = CGroup::GetList($by = "", $order = "", array("STRING_ID" => "content_editor"));
		if($arGroup = $dbGroup->Fetch()){
			$permissions[$arGroup["ID"]] = "W";
		};
		
		// replace macros IN_XML_SITE_ID & IN_XML_SITE_DIR in xml file - for correct url links to site
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back");
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_DIR" => WIZARD_SITE_DIR));
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_ID" => WIZARD_SITE_ID));
		$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, WIZARD_SITE_ID, $permissions);
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		if ($iblockID < 1)	return;
			
		// iblock fields
		$iblock = new CIBlock;
		$arFields = array(
			"ACTIVE" => "Y",
			"CODE" => $iblockCODE,
			"XML_ID" => $iblockXMLID,
			"FIELDS" => array(
				"IBLOCK_SECTION" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "Array",
				),
				"ACTIVE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE"=> "Y",
				),
				"ACTIVE_FROM" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "=today",
				),
				"ACTIVE_TO" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				),
				"SORT" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "0",
				), 
				"NAME" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "",
				), 
				"PREVIEW_PICTURE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"FROM_DETAIL" => "Y",
						"SCALE" => "Y",
						"WIDTH" => "400",
						"HEIGHT" => "400",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
						"DELETE_WITH_DETAIL" => "Y",
						"UPDATE_WITH_DETAIL" => "Y",
					),
				), 
				"PREVIEW_TEXT_TYPE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "text",
				), 
				"PREVIEW_TEXT" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"DETAIL_PICTURE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"SCALE" => "Y",
						"WIDTH" => "2000",
						"HEIGHT" => "2000",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
					),
				), 
				"DETAIL_TEXT_TYPE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "text",
				), 
				"DETAIL_TEXT" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"XML_ID" =>  array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "",
				), 
				"CODE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"UNIQUE" => "Y",
						"TRANSLITERATION" => "Y",
						"TRANS_LEN" => 100,
						"TRANS_CASE" => "L",
						"TRANS_SPACE" => "_",
						"TRANS_OTHER" => "_",
						"TRANS_EAT" => "Y",
						"USE_GOOGLE" => "N",
					),
				),
				"TAGS" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"SECTION_NAME" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "",
				), 
				"SECTION_PICTURE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"FROM_DETAIL" => "Y",
						"SCALE" => "Y",
						"WIDTH" => "120",
						"HEIGHT" => "120",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
						"DELETE_WITH_DETAIL" => "Y",
						"UPDATE_WITH_DETAIL" => "Y",
					),
				), 
				"SECTION_DESCRIPTION_TYPE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => "text",
				), 
				"SECTION_DESCRIPTION" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"SECTION_DETAIL_PICTURE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"SCALE" => "Y",
						"WIDTH" => "2000",
						"HEIGHT" => "2000",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
					),
				), 
				"SECTION_XML_ID" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"SECTION_CODE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => array(
						"UNIQUE" => "Y",
						"TRANSLITERATION" => "Y",
						"TRANS_LEN" => 100,
						"TRANS_CASE" => "L",
						"TRANS_SPACE" => "_",
						"TRANS_OTHER" => "_",
						"TRANS_EAT" => "Y",
						"USE_GOOGLE" => "N",
					),
				), 
			),
		);
		
		$iblock->Update($iblockID, $arFields);
	}
	else{
		// attach iblock to site
		$arSites = array(); 
		$db_res = CIBlock::GetSite($iblockID);
		while ($res = $db_res->Fetch())
			$arSites[] = $res["LID"]; 
		if (!in_array(WIZARD_SITE_ID, $arSites)){
			$arSites[] = WIZARD_SITE_ID;
			$iblock = new CIBlock;
			$iblock->Update($iblockID, array("LID" => $arSites));
		}
	}

	// iblock user fields
	$dbSite = CSite::GetByID(WIZARD_SITE_ID);
	if($arSite = $dbSite -> Fetch()) $lang = $arSite["LANGUAGE_ID"];
	if(!strlen($lang)) $lang = "ru";
	WizardServices::IncludeServiceLang("editform_useroptions.php", $lang);
	WizardServices::IncludeServiceLang("properties_hints.php", $lang);
	$arProperty = array();
	$dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblockID));
	while($arProp = $dbProperty->Fetch())
		$arProperty[$arProp["CODE"]] = $arProp["ID"];

	// properties hints
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["PROP_2033"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_0")));
	unset($ibp);

	// edit form user options
	CUserOptions::SetOption("form", "form_element_".$iblockID, array(
		"tabs" => 'edit1--#--'.GetMessage("WZD_OPTION_80").'--,--ID--#--'.GetMessage("WZD_OPTION_226").'--,--DATE_CREATE--#--'.GetMessage("WZD_OPTION_278").'--,--TIMESTAMP_X--#--'.GetMessage("WZD_OPTION_280").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_2").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_14").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_16").'--,--NAME--#--'.GetMessage("WZD_OPTION_4").'--,--CODE--#--'.GetMessage("WZD_OPTION_6").'--,--XML_ID--#--'.GetMessage("WZD_OPTION_102").'--,--IBLOCK_ELEMENT_PROPERTY--#--'.GetMessage("WZD_OPTION_196").'--,--IBLOCK_ELEMENT_PROP_VALUE--#--'.GetMessage("WZD_OPTION_196").'--,--PROPERTY_'.$arProperty["IN_STOCK"].'--#--'.GetMessage("WZD_OPTION_282").'--,--PROPERTY_'.$arProperty["FAVORIT_ITEM"].'--#--'.GetMessage("WZD_OPTION_284").'--,--PROPERTY_'.$arProperty["BRAND"].'--#--'.GetMessage("WZD_OPTION_168").'--,--PROPERTY_'.$arProperty["SALE_TEXT"].'--#--'.GetMessage("WZD_OPTION_286").'--,--PROPERTY_'.$arProperty["HIT"].'--#--'.GetMessage("WZD_OPTION_288").'--,--PROPERTY_'.$arProperty["HELP_TEXT"].'--#--'.GetMessage("WZD_OPTION_290").'--,--PROPERTY_'.$arProperty["BIG_BLOCK"].'--#--'.GetMessage("WZD_OPTION_292").'--,--PROPERTY_'.$arProperty["BIG_BLOCK_PICTURE"].'--#--'.GetMessage("WZD_OPTION_294").'--,--PROPERTY_'.$arProperty["CML2_ARTICLE"].'--#--'.GetMessage("WZD_OPTION_296").'--,--PROPERTY_'.$arProperty["CML2_BASE_UNIT"].'--#--'.GetMessage("WZD_OPTION_298").'--,--PROPERTY_'.$arProperty["YM_ELEMENT_ID"].'--#--'.GetMessage("WZD_OPTION_300").'--,--edit1_csection1--#--'.GetMessage("WZD_OPTION_302").'--,--PROPERTY_'.$arProperty["PROP_2033"].'--#--'.GetMessage("WZD_OPTION_304").'--,--PROPERTY_'.$arProperty["COLOR_REF2"].'--#--'.GetMessage("WZD_OPTION_306").'--,--PROPERTY_'.$arProperty["PROP_159"].'--#--'.GetMessage("WZD_OPTION_308").'--,--кг--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_2066"].'--#--'.GetMessage("WZD_OPTION_310").'--,--PROPERTY_'.$arProperty["PROP_25"].'--#--'.GetMessage("WZD_OPTION_312").'--,--PROPERTY_'.$arProperty["PROP_2065"].'--#--'.GetMessage("WZD_OPTION_314").'--,--PROPERTY_'.$arProperty["PROP_2083"].'--#--'.GetMessage("WZD_OPTION_316").'--,--PROPERTY_'.$arProperty["PROP_2104"].'--#--'.GetMessage("WZD_OPTION_164").'--,--PROPERTY_'.$arProperty["PROP_2120"].'--#--'.GetMessage("WZD_OPTION_318").'--,--PROPERTY_'.$arProperty["PROP_332"].'--#--'.GetMessage("WZD_OPTION_320").'--,--PROPERTY_'.$arProperty["PROP_2067"].'--#--'.GetMessage("WZD_OPTION_322").'--,--PROPERTY_'.$arProperty["PROP_284"].'--#--'.GetMessage("WZD_OPTION_324").'--,--PROPERTY_'.$arProperty["PROP_315"].'--#--'.GetMessage("WZD_OPTION_326").'--,--см--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_2091"].'--#--'.GetMessage("WZD_OPTION_328").'--,--см--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_307"].'--#--'.GetMessage("WZD_OPTION_330").'--,--см--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_366"].'--#--'.GetMessage("WZD_OPTION_332").'--,--см--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_2084"].'--#--'.GetMessage("WZD_OPTION_334").'--,--PROPERTY_'.$arProperty["PROP_342"].'--#--'.GetMessage("WZD_OPTION_336").'--,--PROPERTY_'.$arProperty["PROP_283"].'--#--'.GetMessage("WZD_OPTION_338").'--,--PROPERTY_'.$arProperty["PROP_2103"].'--#--'.GetMessage("WZD_OPTION_340").'--,--PROPERTY_'.$arProperty["PROP_346"].'--#--'.GetMessage("WZD_OPTION_342").'--,--см--#--'.GetMessage("WZD_OPTION_10").'--,--PROPERTY_'.$arProperty["PROP_2089"].'--#--'.GetMessage("WZD_OPTION_344").'--,--PROPERTY_'.$arProperty["PROP_328"].'--#--'.GetMessage("WZD_OPTION_346").'--,--PROPERTY_'.$arProperty["PROP_308"].'--#--'.GetMessage("WZD_OPTION_348").'--,--PROPERTY_'.$arProperty["PROP_2085"].'--#--'.GetMessage("WZD_OPTION_350").'--,--PROPERTY_'.$arProperty["PROP_309"].'--#--'.GetMessage("WZD_OPTION_352").'--,--PROPERTY_'.$arProperty["PROP_367"].'--#--'.GetMessage("WZD_OPTION_354").'--,--PROPERTY_'.$arProperty["PROP_340"].'--#--'.GetMessage("WZD_OPTION_356").'--,--PROPERTY_'.$arProperty["PROP_2102"].'--#--'.GetMessage("WZD_OPTION_358").'--,--PROPERTY_'.$arProperty["PROP_336"].'--#--'.GetMessage("WZD_OPTION_360").'--,--PROPERTY_'.$arProperty["PROP_2101"].'--#--'.GetMessage("WZD_OPTION_362").'--,--PROPERTY_'.$arProperty["PROP_338"].'--#--'.GetMessage("WZD_OPTION_364").'--,--PROPERTY_'.$arProperty["PROP_337"].'--#--'.GetMessage("WZD_OPTION_366").'--,--PROPERTY_'.$arProperty["PROP_2100"].'--#--'.GetMessage("WZD_OPTION_368").'--,--PROPERTY_'.$arProperty["PROP_349"].'--#--'.GetMessage("WZD_OPTION_370").'--,--PROPERTY_'.$arProperty["PROP_350"].'--#--'.GetMessage("WZD_OPTION_372").'--,--PROPERTY_'.$arProperty["PROP_348"].'--#--'.GetMessage("WZD_OPTION_374").'--,--PROPERTY_'.$arProperty["PROP_341"].'--#--'.GetMessage("WZD_OPTION_376").'--;--edit5--#--'.GetMessage("WZD_OPTION_210").'--,--PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_136").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_176").'--;--edit6--#--'.GetMessage("WZD_OPTION_212").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_138").'--,--PROPERTY_'.$arProperty["MORE_PHOTO"].'--#--'.GetMessage("WZD_OPTION_112").'--,--PROPERTY_'.$arProperty["PHOTO_GALLERY"].'--#--'.GetMessage("WZD_OPTION_378").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_132").'--;--cedit3--#--'.GetMessage("WZD_OPTION_56").'--,--PROPERTY_'.$arProperty["POPUP_VIDEO"].'--#--'.GetMessage("WZD_OPTION_380").'--,--PROPERTY_'.$arProperty["VIDEO_YOUTUBE"].'--#--'.GetMessage("WZD_OPTION_382").'--;--cedit4--#--'.GetMessage("WZD_OPTION_96").'--,--PROPERTY_'.$arProperty["EXPANDABLES_FILTER"].'--#--'.GetMessage("WZD_OPTION_384").'--,--PROPERTY_'.$arProperty["ASSOCIATED_FILTER"].'--#--'.GetMessage("WZD_OPTION_386").'--,--PROPERTY_'.$arProperty["EXPANDABLES"].'--#--'.GetMessage("WZD_OPTION_388").'--,--PROPERTY_'.$arProperty["ASSOCIATED"].'--#--'.GetMessage("WZD_OPTION_390").'--,--PROPERTY_'.$arProperty["INSTRUCTIONS"].'--#--'.GetMessage("WZD_OPTION_262").'--,--PROPERTY_'.$arProperty["SERVICES"].'--#--'.GetMessage("WZD_OPTION_218").'--,--PROPERTY_'.$arProperty["LINK_NEWS"].'--#--'.GetMessage("WZD_OPTION_246").'--,--PROPERTY_'.$arProperty["LINK_BLOG"].'--#--'.GetMessage("WZD_OPTION_260").'--,--PROPERTY_'.$arProperty["LINK_VACANCY"].'--#--'.GetMessage("WZD_OPTION_254").'--,--PROPERTY_'.$arProperty["LINK_STAFF"].'--#--'.GetMessage("WZD_OPTION_242").'--,--PROPERTY_'.$arProperty["LINK_SALE"].'--#--'.GetMessage("WZD_OPTION_222").'--,--PROPERTY_'.$arProperty["PODBORKI"].'--#--'.GetMessage("WZD_OPTION_392").'--;--edit14--#--'.GetMessage("WZD_OPTION_144").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_TITLE--#--'.GetMessage("WZD_OPTION_146").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS--#--'.GetMessage("WZD_OPTION_148").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION--#--'.GetMessage("WZD_OPTION_150").'--,--IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE--#--'.GetMessage("WZD_OPTION_152").'--,--IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_154").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_156").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_158").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_160").'--,--IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_162").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_156").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_158").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_160").'--,--SEO_ADDITIONAL--#--'.GetMessage("WZD_OPTION_164").'--,--TAGS--#--'.GetMessage("WZD_OPTION_166").'--;--edit2--#--'.GetMessage("WZD_OPTION_100").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_100").'--,--IBLOCK_ELEMENT_SECTION_ID--#--'.GetMessage("WZD_OPTION_394").'--;--cedit1--#--'.GetMessage("WZD_OPTION_396").'--,--SORT--#--'.GetMessage("WZD_OPTION_8").'--,--PROPERTY_'.$arProperty["MINIMUM_PRICE"].'--#--'.GetMessage("WZD_OPTION_398").'--,--PROPERTY_'.$arProperty["MAXIMUM_PRICE"].'--#--'.GetMessage("WZD_OPTION_400").'--,--PROPERTY_'.$arProperty["FORUM_MESSAGE_CNT"].'--#--'.GetMessage("WZD_OPTION_402").'--,--PROPERTY_'.$arProperty["vote_count"].'--#--'.GetMessage("WZD_OPTION_404").'--,--PROPERTY_'.$arProperty["rating"].'--#--'.GetMessage("WZD_OPTION_406").'--,--PROPERTY_'.$arProperty["CML2_TRAITS"].'--#--'.GetMessage("WZD_OPTION_408").'--,--PROPERTY_'.$arProperty["CML2_TAXES"].'--#--'.GetMessage("WZD_OPTION_410").'--,--PROPERTY_'.$arProperty["vote_sum"].'--#--'.GetMessage("WZD_OPTION_412").'--,--PROPERTY_'.$arProperty["FORUM_TOPIC_ID"].'--#--'.GetMessage("WZD_OPTION_414").'--,--PROPERTY_'.$arProperty["CML2_ATTRIBUTES"].'--#--'.GetMessage("WZD_OPTION_302").'--;--edit10--#--'.GetMessage("WZD_OPTION_416").'--,--CATALOG--#--'.GetMessage("WZD_OPTION_417").'--;----#--'.GetMessage("WZD_OPTION_10").'--;--',
	));
	// list user options
	CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockTYPE.".".$iblockID), array(
		'columns' => '', 'by' => '', 'order' => '', 'page_size' => '',
	));
}

if($iblockID){
	// replace macros IBLOCK_TYPE & IBLOCK_ID & IBLOCK_CODE
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));
}
?>
