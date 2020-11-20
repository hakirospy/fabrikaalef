<?$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"front_collection", 
	array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"NEWS_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
		"SORT_BY1"	=>	$arParams["ELEMENT_SORT_FIELD"],
		"SORT_ORDER1"	=>	$arParams["ELEMENT_SORT_ORDER"],
		"SORT_BY2"	=>	$arParams["ELEMENT_SORT_FIELD2"],
		"SORT_ORDER2"	=>	$arParams["ELEMENT_SORT_ORDER2"],
		"FILTER_NAME"	=>	$arParams["FILTER_NAME"],
		"FIELD_CODE"	=>	$arParams["LIST_FIELD_CODE"],
		"PROPERTY_CODE"	=>	$arParams["LIST_PROPERTY_CODE"],
		"CHECK_DATES"	=>	$arParams["CHECK_DATES"],
		"DETAIL_URL"	=>	$arResult["SEF_FOLDER"].$arResult["SEF_URL_TEMPLATES"]["element"],
		"SECTION_URL"	=>	$arResult["SEF_FOLDER"].$arResult["SEF_URL_TEMPLATES"]["section"],
		"IBLOCK_URL"	=>	$arResult["SEF_FOLDER"].$arResult["SEF_URL_TEMPLATES"]["sections"],
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
		"CACHE_TIME"	=>	$arParams["CACHE_TIME"],
		"CACHE_FILTER"	=>	$arParams["CACHE_FILTER"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"PREVIEW_TRUNCATE_LEN"	=>	$arParams["PREVIEW_TRUNCATE_LEN"],
		"ACTIVE_DATE_FORMAT"	=>	$arParams["LIST_ACTIVE_DATE_FORMAT"],
		"SET_TITLE"	=>	$arParams["SET_TITLE"],
		"SHOW_DETAIL_LINK"	=>	$arParams["SHOW_DETAIL_LINK"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"INCLUDE_IBLOCK_INTO_CHAIN"	=>	$arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
		"ADD_SECTIONS_CHAIN" => "N",
		"HIDE_LINK_WHEN_NO_DETAIL"	=>	$arParams["HIDE_LINK_WHEN_NO_DETAIL"],
		"PARENT_SECTION"	=>	$arResult["VARIABLES"]["SECTION_ID"],
		"PARENT_SECTION_CODE"	=>	$arResult["VARIABLES"]["SECTION_CODE"],
		"DISPLAY_TOP_PAGER"	=>	$arParams["DISPLAY_TOP_PAGER"],
		"DISPLAY_BOTTOM_PAGER"	=>	$arParams["DISPLAY_BOTTOM_PAGER"],
		"PAGER_TITLE"	=>	$arParams["PAGER_TITLE"],
		"PAGER_TEMPLATE"	=>	$arParams["PAGER_TEMPLATE"],
		"PAGER_SHOW_ALWAYS"	=>	$arParams["PAGER_SHOW_ALWAYS"],
		"PAGER_DESC_NUMBERING"	=>	$arParams["PAGER_DESC_NUMBERING"],
		"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
		"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "N",
		"DISPLAY_PREVIEW_TEXT" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => "front_collection",
		"SET_BROWSER_TITLE" => "N",
		"SET_LAST_MODIFIED" => "N",
		"INCLUDE_SUBSECTIONS" => "Y",//"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
		"STRICT_SECTION_CHECK" => "N",
		"TITLE_BLOCK" => "",
		"TITLE_BLOCK_ALL" => "",
		"SHOW_ADD_REVIEW" => "Y",
		"VIEW_TYPE" => "bg_img",
		"ALL_URL" => "landings/",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"COMPACT" => "Y",
		"IS_AJAX" => CMax::checkAjaxRequest(),
		"SIZE_IN_ROW" => $arParams["SIZE_IN_ROW"],
		"MESSAGE_404" => ""
	),
	$component
);

?>
 