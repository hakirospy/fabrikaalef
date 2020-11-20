<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Sotbit\Seometa\SitemapTable;
use Sotbit\Seometa\ConditionTable;
use Sotbit\Seometa\SeometaUrlTable;
use Bitrix\Main\Loader;
use Bitrix\Seo\SitemapRuntime;

Loc::loadMessages(__FILE__);

if(!$USER->CanDoOperation('sotbit.seometa'))
{
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if($_REQUEST['action'] == 'check_status') {
    if(file_exists($_SERVER['DOCUMENT_ROOT'] ."/seometa_data_tmp.txt"))
        echo file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/seometa_data_tmp.txt");
    else
        echo 'stop';
    exit;
}

Loader::includeModule('sotbit.seometa');
$ID = intval($_REQUEST['ID']);
$arSitemap = null;

if($ID > 0)
{
    $dbSitemap = SitemapTable::getById($ID);
    $arSitemap = $dbSitemap->fetch();
}

if(!is_array($arSitemap))
{
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError(Loc::getMessage("SEO_META_ERROR_SITEMAP_NOT_FOUND"));
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}
else
{
    $arSitemap['SETTINGS'] = unserialize($arSitemap['SETTINGS']);
}

$arSites = array();
$rsSites = CSite::GetById($arSitemap['SITE_ID']);

$arSite = $rsSites->Fetch();
$SiteUrl = "";

if(isset($arSitemap['SETTINGS']['PROTO']) && $arSitemap['SETTINGS']['PROTO'] == 1)
{
    $SiteUrl .= 'https://';
}
elseif(isset($arSitemap['SETTINGS']['PROTO']) && $arSitemap['SETTINGS']['PROTO'] == 0)
{
    $SiteUrl .= 'http://';
}
else
{
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError(Loc::getMessage("SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND"));
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

if(isset($arSitemap['SETTINGS']['DOMAIN']) && !empty($arSitemap['SETTINGS']['DOMAIN']))
    $SiteUrl .= $arSitemap['SETTINGS']['DOMAIN'] . substr($arSite['DIR'], 0, -1);
else
{
    ShowError(Loc::getMessage("SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND"));
}

if(isset($arSitemap['SETTINGS']['FILENAME_INDEX']) && !empty($arSitemap['SETTINGS']['FILENAME_INDEX']))
    $mainSitemapName = $arSitemap['SETTINGS']['FILENAME_INDEX'];
else
{
    ShowError(Loc::getMessage("SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND"));
}

if(isset($arSitemap['SETTINGS']['FILTER_TYPE']) && !is_null($arSitemap['SETTINGS']['FILTER_TYPE']))
{
    $FilterTypeKey = key($arSitemap['SETTINGS']['FILTER_TYPE']);
    $FilterCHPU = $arSitemap['SETTINGS']['FILTER_TYPE'][$FilterTypeKey];

    $FilterType = strtolower($FilterTypeKey . ((!$FilterCHPU) ? '_not' : '') . '_chpu');
}
else
{
    ShowError(Loc::getMessage("SEO_META_ERROR_SITEMAP_FILTER_TYPE_NOT_FOUND"));
}

$mainSitemapUrl = $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . $mainSitemapName;

if(file_exists($mainSitemapUrl))
{
//    $FoundSeoMetaSitemap = false;
//    $xml = simplexml_load_file($mainSitemapUrl);

    if(isset($action) && ($action == 'get_section_list')){
        file_put_contents($_SERVER['DOCUMENT_ROOT']. '/seometa_data_tmp.txt', '0');

        $link = \Sotbit\Seometa\Helper\Link::getInstance();
        echo json_encode($link->getSectionList($ID));
        exit;
    }

//    for($i = 0; $i < count($xml->sitemap); $i++)
//    {
//        if(isset($xml->sitemap[$i]->loc) && $xml->sitemap[$i]->loc == $SiteUrl . '/sitemap_seometa_' . $ID . '.xml')
//        {
//            $FoundSeoMetaSitemap = true;
//            $xml->sitemap[$i]->lastmod = date('Y-m-d\TH:i:sP');
//        }
//    }

//    if(!$FoundSeoMetaSitemap) // if sitemap_seometa is not found then add it to main sitemap
//    {
//        $NewSitemap = $xml->addChild("sitemap");
//        $NewSitemap->addChild("loc", $SiteUrl . '/sitemap_seometa_' . $ID . '.xml');
//        $NewSitemap->addChild("lastmod", date('Y-m-d\TH:i:sP'));
//        $NewSitemap->addChild("lastmod", (isset($arSitemap['DATE_RUN']) && !empty($arSitemap['DATE_RUN'])) ? str_replace(' ', 'T', date('Y-m-d H:i:sP', strtotime($arSitemap['DATE_RUN']))) : str_replace(' ', 'T', date('Y-m-d H:i:sP', strtotime($arSitemap['TIMESTAMP_CHANGE']))));
//    }

//    file_put_contents($mainSitemapUrl, $xml->asXML());

    // START GENERATE XML ARRAY
    $rsCondition = ConditionTable::getList(array(
        'select' => array(
            'ID',
            'DATE_CHANGE',
            'INFOBLOCK',
            'STRONG',
            'NO_INDEX',
            'RULE',
            'SITES',
            'SECTIONS',
            'PRIORITY',
            'CHANGEFREQ',
        ),
        'filter' => array(
            'ACTIVE' => 'Y',
            '!=NO_INDEX' => 'Y',
        ),
        'order' => array(
            'ID' => 'asc'
        )
    ));

    $connection = Application::getConnection();
    $writer = \Sotbit\Seometa\Link\XmlWriter::getInstance($ID, $arSite['ABS_DOC_ROOT'] . $arSite['DIR'], $SiteUrl, $iteration);

    //$start = microtime(true);
    while($arCondition = $rsCondition->Fetch())
    {
        // if condition belongs to the site for which sitemap is generated
        if(in_array($arSitemap['SITE_ID'], unserialize($arCondition['SITES'])))
        {
            $rule = unserialize($arCondition['RULE']);
            if(empty($rule['CHILDREN']))
                continue;

            // reset all 'IN_SITEMAP' statuses before new generation of sitemap
            $arrIds = SeometaUrlTable::getArrIdsByConditionId($arCondition['ID']);
            if($arrIds)
            {
                $sql = "UPDATE `b_sotbit_seometa_chpu` SET `IN_SITEMAP` = 'N' WHERE `b_sotbit_seometa_chpu`.`ID` IN (" . $arrIds . ")";
                $res = $connection->query($sql);
            }

            $link = \Sotbit\Seometa\Helper\Link::getInstance();
            if (isset($currentSection) && (is_numeric($currentSection))) {
                $link->Generate($arCondition['ID'], $writer, array($currentSection));
//                echo SitemapRuntime::showProgress(Loc::getMessage('SEO_META_SITEMAP_RUN_INIT'), Loc::getMessage('SEO_META_SITEMAP_RUN_TITLE'), ($iteration+1)*100/$sectionCount);
                //last iteration
//                if (($iteration+1) == $sectionCount){
//                    $writer->WriteEnd();
//                    SitemapTable::update($ID, array('DATE_RUN' => new Bitrix\Main\Type\DateTime()));
//                }
            } else {
                $link->Generate($arCondition['ID'], $writer);
            }

//            SitemapTable::update($ID, array('DATE_RUN' => new Bitrix\Main\Type\DateTime()));

            // if regeneration option is enabled
            //$writerForRegenerate = \Sotbit\Seometa\Link\ChpuWriter::getWriterForSitemap($arCondition['ID']);
            //$link->Generate($arCondition['ID'], $writerForRegenerate);
        }
    }

    //echo 'Execution Time: ' . round(microtime(true) - $start, 4) . ' sec.';
    $writer->WriteEnd();
    $writer->writeMainSiteMap();

    SitemapTable::update($ID, array('DATE_RUN' => new Bitrix\Main\Type\DateTime()));
    ?>
    <script>
        // top.BX.finishSitemap();
    </script>
    <?
//    exit();
}
else
{
    ShowError(Loc::getMessage("SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND") . ' ' . $mainSitemapUrl);
}
?>