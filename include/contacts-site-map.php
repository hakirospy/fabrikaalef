<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>


<?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view", 
	"map", 
	array(
		"API_KEY" => "",
		"CONTROLS" => array(
			0 => "ZOOM",
			1 => "TYPECONTROL",
			2 => "SCALELINE",
		),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:5:{s:10:\"yandex_lat\";d:44.056926767041006;s:10:\"yandex_lon\";d:42.990792065640974;s:12:\"yandex_scale\";i:18;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:42.99092440796164;s:3:\"LAT\";d:44.05697034239967;s:4:\"TEXT\";s:53:\"Магазин верхней одежды \"АЛЕФ\"\";}}s:9:\"POLYLINES\";a:1:{i:0;a:3:{s:6:\"POINTS\";a:2:{i:0;a:2:{s:3:\"LAT\";d:44.05680877602;s:3:\"LON\";d:42.990534573576;}i:1;a:2:{s:3:\"LAT\";d:44.056669507651;s:3:\"LON\";d:42.990228801748;}}s:5:\"TITLE\";s:26:\"Вход в магазин\";s:5:\"STYLE\";a:2:{s:11:\"strokeColor\";s:8:\"FFDD99FF\";s:11:\"strokeWidth\";i:6;}}}}",
		"MAP_HEIGHT" => "100%",
		"MAP_ID" => "",
		"MAP_WIDTH" => "100%",
		"OPTIONS" => array(
			0 => "ENABLE_DBLCLICK_ZOOM",
			1 => "ENABLE_DRAGGING",
		),
		"USE_REGION_DATA" => "Y",
		"COMPONENT_TEMPLATE" => "map"
	),
	false
);?>