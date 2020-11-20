"STAGES" => Array(
	"banner_types.php"
	"vacancy.php"
	"licenses.php"
	"regions.php"
	"faq.php"
	"megamenu.php"
	"docs.php"
	"adv_content.php"
	"banners_inner.php"
	"add_review.php"
	"tizers.php"
	"shops.php"
	"company.php"
	"banners_float.php"
	"cross_sales.php"
	"landing.php"
	"search.php"
	"stock.php"
	"partners.php"
	"catalog.php"
	"banners.php"
	"news.php"
	"services.php"
	"sku.php"
	"projects.php"
	"catalog_info.php"
	"articles.php"
	"brands.php"
	"staff.php"
),
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;
if(!CModule::IncludeModule("aspro.max")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";


// iblocks ids
$partnersIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_partners"][0];
$brandsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_brands"][0];
$newsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_news"][0];
$stockIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_stock"][0];
$catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0];
$regionsIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_regionality"]["aspro_max_regions"][0];
$articlesIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_content"]["aspro_max_articles"][0];
$searchIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_search"][0];
$cross_salesIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_cross_sales"][0];


// elements ids
$arCatalog = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($catalogIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $catalogIBlockID), false, false, array("ID", "XML_ID"));
$arStock = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($stockIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $stockIBlockID), false, false, array("ID", "XML_ID"));
$arArticles = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($articlesIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $articlesIBlockID), false, false, array("ID", "XML_ID"));
$arBrands = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($brandsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $brandsIBlockID), false, false, array("ID", "XML_ID"));
$arNews = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($newsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $newsIBlockID), false, false, array("ID", "XML_ID"));
$arRegions = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($regionsIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $regionsIBlockID), false, false, array("ID", "XML_ID"));
$arPartners = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($partnersIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $partnersIBlockID), false, false, array("ID", "XML_ID"));
$arStock = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($stockIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $stockIBlockID), false, false, array("ID", "XML_ID"));
$arSearch = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($searchIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $searchIBlockID), false, false, array("ID", "XML_ID"));
$arCross_sales = CMaxCache::CIBlockElement_GetList(array("CACHE" => array("TIME" => 0, "TAG" => CMaxCache::GetIBlockCacheTag($cross_salesIBlockID), "GROUP" => array("XML_ID"), "RESULT" => array("ID"))), array("IBLOCK_ID" => $cross_salesIBlockID), false, false, array("ID", "XML_ID"));



// update links in aspro_max_stock
CIBlockElement::SetPropertyValuesEx($arStock["49859"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["325"], $arCatalog["3752"], $arCatalog["3518"], $arCatalog["382"], $arCatalog["3743"], $arCatalog["3571"], $arCatalog["3317"], $arCatalog["3444"], $arCatalog["3434"], $arCatalog["3727"], $arCatalog["3732"], $arCatalog["299"])));
CIBlockElement::SetPropertyValuesEx($arStock["3252"], $stockIBlockID, array("LINK_GOODS" => array($arCatalog["3342"], $arCatalog["3492"], $arCatalog["3548"], $arCatalog["3512"], $arCatalog["3528"], $arCatalog["294"], $arCatalog["299"], $arCatalog["3434"], $arCatalog["382"])));

// update links in aspro_max_catalog
CIBlockElement::SetPropertyValuesEx($arCatalog["8951"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["3791"]), "ASSOCIATED" => array($arCatalog["448"], $arCatalog["5659"], $arCatalog["3537"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["448"], $catalogIBlockID, array("BRAND" => array($arBrands["3790"]), "ASSOCIATED" => array($arCatalog["8951"], $arCatalog["5659"], $arCatalog["3537"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["5659"], $catalogIBlockID, array("BRAND" => array($arBrands["3792"]), "ASSOCIATED" => array($arCatalog["8951"], $arCatalog["448"], $arCatalog["3537"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3537"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["3792"]), "ASSOCIATED" => array($arCatalog["8951"], $arCatalog["5659"], $arCatalog["448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["321"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3537"], $arCatalog["448"], $arCatalog["3324"], $arCatalog["3738"], $arCatalog["5659"], $arCatalog["8951"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["346"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["8951"], $arCatalog["3537"], $arCatalog["3501"], $arCatalog["3317"], $arCatalog["3487"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["325"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["8951"], $arCatalog["5659"], $arCatalog["3537"], $arCatalog["448"], $arCatalog["3329"], $arCatalog["3434"], $arCatalog["333"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["333"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["325"], $arCatalog["3317"], $arCatalog["448"], $arCatalog["3329"]), "ASSOCIATED" => array($arCatalog["346"], $arCatalog["321"], $arCatalog["3737"], $arCatalog["3434"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3434"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3738"], $arCatalog["325"], $arCatalog["3444"], $arCatalog["3317"]), "ASSOCIATED" => array($arCatalog["321"], $arCatalog["3737"], $arCatalog["346"], $arCatalog["333"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3444"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3537"], $arCatalog["3737"], $arCatalog["3317"], $arCatalog["3434"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3737"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3537"], $arCatalog["3317"], $arCatalog["3434"], $arCatalog["3738"]), "ASSOCIATED" => array($arCatalog["346"], $arCatalog["333"], $arCatalog["321"], $arCatalog["3434"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3738"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3434"], $arCatalog["3737"], $arCatalog["346"], $arCatalog["3317"], $arCatalog["3329"], $arCatalog["5659"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["382"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49905"]), "BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3539"], $arCatalog["3518"], $arCatalog["3746"], $arCatalog["395"]), "ASSOCIATED" => array($arCatalog["395"], $arCatalog["3743"], $arCatalog["3747"], $arCatalog["3751"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["395"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["3422"]), "BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3746"], $arCatalog["3752"], $arCatalog["382"], $arCatalog["3524"], $arCatalog["3518"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3743"], $catalogIBlockID, array("BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3746"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["382"], $arCatalog["3752"], $arCatalog["3518"], $arCatalog["3751"], $arCatalog["3747"], $arCatalog["395"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3747"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3539"], $arCatalog["3746"], $arCatalog["3524"], $arCatalog["3756"]), "ASSOCIATED" => array($arCatalog["3751"], $arCatalog["3743"], $arCatalog["395"], $arCatalog["382"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3751"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3539"], $arCatalog["3518"], $arCatalog["382"], $arCatalog["3747"], $arCatalog["395"], $arCatalog["3524"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3317"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["346"], $arCatalog["3444"], $arCatalog["3434"], $arCatalog["333"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3324"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3738"], $arCatalog["321"], $arCatalog["8951"], $arCatalog["448"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3329"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3738"], $arCatalog["321"], $arCatalog["333"], $arCatalog["3434"]), "ASSOCIATED" => array($arCatalog["3317"], $arCatalog["3501"], $arCatalog["3324"], $arCatalog["3487"], $arCatalog["3492"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3487"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["333"], $arCatalog["346"], $arCatalog["321"], $arCatalog["3738"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3492"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["333"], $arCatalog["3537"], $arCatalog["325"], $arCatalog["3434"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3501"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3738"], $arCatalog["5659"], $arCatalog["346"], $arCatalog["333"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3334"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3719"], $arCatalog["313"], $arCatalog["299"], $arCatalog["309"]), "ASSOCIATED" => array($arCatalog["3351"], $arCatalog["3347"], $arCatalog["3727"], $arCatalog["3732"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3342"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3288"], $arCatalog["294"], $arCatalog["299"], $arCatalog["309"]), "ASSOCIATED" => array($arCatalog["3347"], $arCatalog["3351"], $arCatalog["3727"], $arCatalog["3732"], $arCatalog["3334"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3347"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3452"], $arCatalog["3719"], $arCatalog["313"], $arCatalog["309"]), "ASSOCIATED" => array($arCatalog["3727"], $arCatalog["3351"], $arCatalog["3732"], $arCatalog["3334"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3351"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["294"], $arCatalog["313"], $arCatalog["299"], $arCatalog["309"]), "ASSOCIATED" => array($arCatalog["3347"], $arCatalog["3727"], $arCatalog["3732"], $arCatalog["3334"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3727"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["299"], $arCatalog["309"], $arCatalog["313"], $arCatalog["3712"]), "ASSOCIATED" => array($arCatalog["3351"], $arCatalog["3347"], $arCatalog["3732"], $arCatalog["3334"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3732"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["299"], $arCatalog["309"], $arCatalog["3712"], $arCatalog["3710"]), "ASSOCIATED" => array($arCatalog["3342"], $arCatalog["3334"], $arCatalog["3727"], $arCatalog["3347"], $arCatalog["3351"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3512"], $catalogIBlockID, array("BRAND" => array($arBrands["293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3518"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3524"], $catalogIBlockID, array("BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3528"], $catalogIBlockID, array("BRAND" => array($arBrands["293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3548"], $catalogIBlockID, array("BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3554"], $catalogIBlockID, array("BRAND" => array($arBrands["293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3565"], $catalogIBlockID, array("BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3571"], $catalogIBlockID, array("BRAND" => array($arBrands["293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["294"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3710"], $arCatalog["3461"], $arCatalog["3451"], $arCatalog["3342"], $arCatalog["3351"]), "ASSOCIATED" => array($arCatalog["3698"], $arCatalog["3692"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3692"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3461"], $arCatalog["3462"], $arCatalog["3451"], $arCatalog["3452"], $arCatalog["3342"], $arCatalog["3719"], $arCatalog["299"]), "ASSOCIATED" => array($arCatalog["3698"], $arCatalog["294"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3698"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3342"], $arCatalog["3351"], $arCatalog["3461"], $arCatalog["3288"], $arCatalog["3452"]), "ASSOCIATED" => array($arCatalog["294"], $arCatalog["3692"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["313"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3334"], $arCatalog["3347"], $arCatalog["299"], $arCatalog["309"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3710"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3719"], $arCatalog["294"], $arCatalog["309"], $arCatalog["3712"], $arCatalog["3720"], $arCatalog["299"], $arCatalog["3451"], $arCatalog["3452"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3711"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["49914"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3712"], $arCatalog["309"], $arCatalog["3288"], $arCatalog["3347"], $arCatalog["3289"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["309"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49905"]), "BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["313"], $arCatalog["299"], $arCatalog["3334"], $arCatalog["3351"], $arCatalog["3342"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3712"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3451"], $arCatalog["3461"], $arCatalog["3727"], $arCatalog["3732"], $arCatalog["299"], $arCatalog["3720"]), "ASSOCIATED" => array($arCatalog["3713"], $arCatalog["309"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3713"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49905"]), "BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3334"], $arCatalog["3719"], $arCatalog["313"], $arCatalog["3462"], $arCatalog["3451"]), "ASSOCIATED" => array($arCatalog["309"], $arCatalog["3712"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["299"], $catalogIBlockID, array("BRAND" => array($arBrands["293"]), "EXPANDABLES" => array($arCatalog["3732"], $arCatalog["309"], $arCatalog["313"], $arCatalog["3342"], $arCatalog["3727"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3719"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3289"], $arCatalog["3452"], $arCatalog["3461"], $arCatalog["3713"], $arCatalog["3712"], $arCatalog["3351"], $arCatalog["309"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3720"], $catalogIBlockID, array("BRAND" => array($arBrands["359"]), "EXPANDABLES" => array($arCatalog["3462"], $arCatalog["3732"], $arCatalog["3727"], $arCatalog["309"], $arCatalog["3712"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3288"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3289"], $catalogIBlockID, array("BRAND" => array($arBrands["3304"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3451"], $catalogIBlockID, array("BRAND" => array($arBrands["49670"]), "EXPANDABLES" => array($arCatalog["3288"]), "ASSOCIATED" => array($arCatalog["3288"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3452"], $catalogIBlockID, array("BRAND" => array($arBrands["397"]), "EXPANDABLES" => array($arCatalog["3288"]), "ASSOCIATED" => array($arCatalog["3288"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3461"], $catalogIBlockID, array("BRAND" => array($arBrands["49670"]), "ASSOCIATED" => array($arCatalog["3462"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3462"], $catalogIBlockID, array("BRAND" => array($arBrands["397"]), "ASSOCIATED" => array($arCatalog["3461"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3539"], $catalogIBlockID, array("BRAND" => array($arBrands["3790"]), "ASSOCIATED" => array($arCatalog["3538"], $arCatalog["3540"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3752"], $catalogIBlockID, array("BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3756"], $catalogIBlockID, array("BRAND" => array($arBrands["293"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["400"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3774"], $arCatalog["3548"], $arCatalog["3571"], $arCatalog["382"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["408"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3759"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["3770"], $arCatalog["3571"], $arCatalog["3565"], $arCatalog["3540"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3766"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49909"]), "BRAND" => array($arBrands["401"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3767"], $catalogIBlockID, array("BRAND" => array($arBrands["401"]), "EXPANDABLES" => array($arCatalog["400"], $arCatalog["3548"], $arCatalog["3774"], $arCatalog["3538"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3770"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "EXPANDABLES" => array($arCatalog["3759"], $arCatalog["3565"], $arCatalog["3554"], $arCatalog["3774"], $arCatalog["3540"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3538"], $catalogIBlockID, array("BRAND" => array($arBrands["3792"]), "ASSOCIATED" => array($arCatalog["3539"], $arCatalog["3540"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3540"], $catalogIBlockID, array("BRAND" => array($arBrands["3790"]), "ASSOCIATED" => array($arCatalog["3538"], $arCatalog["3539"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3774"], $catalogIBlockID, array("BRAND" => array($arBrands["359"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["360"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["3787"], $arCatalog["350"], $arCatalog["364"], $arCatalog["3778"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["350"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["49907"]), "LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3778"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["350"], $arCatalog["360"], $arCatalog["3783"], $arCatalog["3779"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["364"], $catalogIBlockID, array("LINK_BLOG" => array($arArticles["268"]), "BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3779"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["350"], $arCatalog["3778"], $arCatalog["355"], $arCatalog["360"], $arCatalog["3787"], $arCatalog["3786"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3783"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["355"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["364"], $arCatalog["355"], $arCatalog["350"], $arCatalog["360"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3786"], $catalogIBlockID, array("LINK_NEWS" => array($arNews["10633"]), "BRAND" => array($arBrands["320"])));
CIBlockElement::SetPropertyValuesEx($arCatalog["3787"], $catalogIBlockID, array("BRAND" => array($arBrands["320"]), "ASSOCIATED" => array($arCatalog["350"], $arCatalog["360"], $arCatalog["3778"], $arCatalog["3786"], $arCatalog["355"])));

// update links in aspro_max_partners
CIBlockElement::SetPropertyValuesEx($arPartners["27"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["28"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"])));
CIBlockElement::SetPropertyValuesEx($arPartners["1762"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["67"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"])));
CIBlockElement::SetPropertyValuesEx($arPartners["69"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3213"], $arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));
CIBlockElement::SetPropertyValuesEx($arPartners["70"], $partnersIBlockID, array("LINK_REGION" => array($arRegions["3212"], $arRegions["3215"], $arRegions["3214"])));


// get sections
$newPropSectionsXML = array (
  211 => '3462bba5-69c4-11dd-a899-0018f3099a8f',
  401 => '401',
);
$newPropSectionsRes = CIBlockSection::GetList(array(), array("XML_ID" => $newPropSectionsXML, "IBLOCK_ID" => $catalogIBlockID), false, array("ID", "XML_ID"));
while($newPropSection = $newPropSectionsRes->Fetch()) {
	$arNewPropSections[$newPropSection["XML_ID"]] = $newPropSection["ID"];
}

// get props
$newPropPropsXML = array (
  896 => '896',
);
foreach($newPropPropsXML as $xml) {
	$resNewProps = CIBlockProperty::GetList(
		array(),
		array("XML_ID" => $xml)
	);
	$newProp = $resNewProps->Fetch();
	$arNewPropProps[$newProp["XML_ID"]] = $newProp["ID"];
}

// update values custom filter
CIBlockElement::SetPropertyValuesEx($arNews["49905"], $newsIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"OR","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBProp:'.CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0].':'.$arNewPropProps["896"].'","DATA":{"logic":"Equal","value":"Осень"}},{"CLASS_ID":"CondIBProp:'.CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0].':'.$arNewPropProps["896"].'","DATA":{"logic":"Equal","value":"Зима"}}]}'));
CIBlockElement::SetPropertyValuesEx($arStock["3253"], $stockIBlockID, array("LINK_GOODS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":{"1":{"CLASS_ID":"CondIBProp:'.CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0].':'.$arNewPropProps["896"].'","DATA":{"logic":"Equal","value":"Осень"}}}}'));
CIBlockElement::SetPropertyValuesEx($arSearch["3364"], $searchIBlockID, array("CUSTOM_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["3462bba5-69c4-11dd-a899-0018f3099a8f"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3788"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["401"].'"}}]}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["401"].'"}}]}'));
CIBlockElement::SetPropertyValuesEx($arCross_sales["3789"], $cross_salesIBlockID, array("PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["3462bba5-69c4-11dd-a899-0018f3099a8f"].'"}}]}', "EXT_PRODUCTS_FILTER" => '{"CLASS_ID":"CondGroup","DATA":{"All":"AND","True":"True"},"CHILDREN":[{"CLASS_ID":"CondIBSection","DATA":{"logic":"Equal","value":"'.$arNewPropSections["3462bba5-69c4-11dd-a899-0018f3099a8f"].'"}}]}'));
?>