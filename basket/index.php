<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?>
<script>window.basketJSParams = window.basketJSParams || [];</script>
<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", "template1", Array(
	"COLUMNS_LIST" => array(
			0 => "NAME",
			1 => "DISCOUNT",
			2 => "PROPS",
			3 => "DELETE",
			4 => "DELAY",
			5 => "TYPE",
			6 => "PRICE",
			7 => "QUANTITY",
			8 => "SUM",
		),
		"OFFERS_PROPS" => array(
			0 => "SIZES",
			1 => "COLOR_REF",
		),
		"PATH_TO_ORDER" => SITE_DIR."order/",	// Страница оформления заказа
		"HIDE_COUPON" => "N",	// Спрятать поле ввода купона
		"PRICE_VAT_SHOW_VALUE" => "Y",	// Отображать значение НДС
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"USE_PREPAYMENT" => "N",	// Использовать предавторизацию для оформления заказа (PayPal Express Checkout)
		"SET_TITLE" => "N",	// Устанавливать заголовок страницы
		"AJAX_MODE_CUSTOM" => "Y",
		"SHOW_MEASURE" => "Y",
		"PICTURE_WIDTH" => "100",
		"PICTURE_HEIGHT" => "100",
		"SHOW_FULL_ORDER_BUTTON" => "Y",
		"SHOW_FAST_ORDER_BUTTON" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"QUANTITY_FLOAT" => "N",	// Использовать дробное значение количества
		"ACTION_VARIABLE" => "action",	// Название переменной действия
		"TEMPLATE_THEME" => "blue",	// Цветовая тема
		"AUTO_CALCULATION" => "Y",	// Автопересчет корзины
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"USE_GIFTS" => "Y",	// Показывать блок "Подарки"
		"GIFTS_PLACE" => "BOTTOM",	// Вывод блока "Подарки"
		"GIFTS_BLOCK_TITLE" => "Выберите один из подарков",	// Текст заголовка "Подарки"
		"GIFTS_HIDE_BLOCK_TITLE" => "N",	// Скрыть заголовок "Подарки"
		"GIFTS_TEXT_LABEL_GIFT" => "Подарок",	// Текст метки "Подарка"
		"GIFTS_PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
		"GIFTS_PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
		"GIFTS_SHOW_OLD_PRICE" => "Y",	// Показывать старую цену
		"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",	// Показывать процент скидки
		"GIFTS_SHOW_NAME" => "Y",
		"GIFTS_SHOW_IMAGE" => "Y",
		"GIFTS_MESS_BTN_BUY" => "Выбрать",	// Текст кнопки "Выбрать"
		"GIFTS_MESS_BTN_DETAIL" => "Подробнее",	// Текст кнопки "Подробнее"
		"GIFTS_PAGE_ELEMENT_COUNT" => "4",	// Количество элементов в строке
		"GIFTS_CONVERT_CURRENCY" => "N",	// Показывать цены в одной валюте
		"GIFTS_HIDE_NOT_AVAILABLE" => "N",	// Не отображать товары, которых нет на складах
		"EMPTY_BASKET_HINT_PATH" => SITE_DIR."catalog/",	// Путь к странице для продолжения покупок
		"DEFERRED_REFRESH" => "N",	// Использовать механизм отложенной актуализации данных товаров с провайдером
		"USE_DYNAMIC_SCROLL" => "Y",	// Использовать динамическую подгрузку товаров
		"SHOW_FILTER" => "Y",	// Отображать фильтр товаров
		"SHOW_RESTORE" => "Y",	// Разрешить восстановление удалённых товаров
		"COLUMNS_LIST_EXT" => array(	// Выводимые колонки
			0 => "PREVIEW_PICTURE",
			1 => "DISCOUNT",
			2 => "DELETE",
			3 => "DELAY",
			4 => "TYPE",
			5 => "SUM",
			6 => "PROPERTY_TSVET",
		),
		"COLUMNS_LIST_MOBILE" => array(	// Колонки, отображаемые на мобильных устройствах
			0 => "PREVIEW_PICTURE",
			1 => "DISCOUNT",
			2 => "DELETE",
			3 => "DELAY",
			4 => "TYPE",
			5 => "SUM",
		),
		"TOTAL_BLOCK_DISPLAY" => array(	// Отображение блока с общей информацией по корзине
			0 => "top",
		),
		"DISPLAY_MODE" => "extended",	// Режим отображения корзины
		"PRICE_DISPLAY_MODE" => "Y",	// Отображать цену в отдельной колонке
		"SHOW_DISCOUNT_PERCENT" => "Y",	// Показывать процент скидки рядом с изображением
		"DISCOUNT_PERCENT_POSITION" => "bottom-right",	// Расположение процента скидки
		"PRODUCT_BLOCKS_ORDER" => "props,sku,columns",	// Порядок отображения блоков товара
		"USE_PRICE_ANIMATION" => "Y",	// Использовать анимацию цен
		"LABEL_PROP" => "",	// Свойства меток товара
		"CORRECT_RATIO" => "Y",	// Автоматически рассчитывать количество товара кратное коэффициенту
		"COMPATIBLE_MODE" => "Y",	// Включить режим совместимости
		"ADDITIONAL_PICT_PROP_21" => "-",	// Дополнительная картинка [Каталог товаров]
		"ADDITIONAL_PICT_PROP_46" => "-",	// Дополнительная картинка [Пакет предложений (Каталог товаров)]
		"BASKET_IMAGES_SCALING" => "adaptive",	// Режим отображения изображений товаров
		"USE_ENHANCED_ECOMMERCE" => "N",	// Отправлять данные электронной торговли в Google и Яндекс
	),
	false
);?>

<?$APPLICATION->IncludeComponent(
	"bitrix:main.include", 
	"basket", 
	array(
		"COMPONENT_TEMPLATE" => "basket",
		"PATH" => SITE_DIR."include/comp_basket_bigdata.php",
		"AREA_FILE_SHOW" => "file",
		"AREA_FILE_SUFFIX" => "",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "standard.php",
		"PRICE_CODE" => array(
			0 => "BASE",
			1 => "OPT",
		),
		"STORES" => array(
			0 => "1",
			1 => "2",
			2 => "",
		),
		"BIG_DATA_RCM_TYPE" => "personal",
		"STIKERS_PROP" => "HIT",
		"SALE_STIKER" => "SALE_TEXT"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>