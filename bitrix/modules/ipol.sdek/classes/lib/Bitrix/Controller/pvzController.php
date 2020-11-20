<?php

namespace Ipolh\SDEK\Bitrix\Controller;


use Ipolh\SDEK\Bitrix\Tools;

class pvzController extends abstractController
{
    protected static $arList = false;

    protected static $cacheTime = 86400;

    public function __construct()
    {
        $this->loadPVZ();
        return $this;
    }

    public function getList()
    {
        return self::$arList;
    }

    /**
     * old usage
     */
    public function getListFile()
    {
        $arList = array();
        foreach (self::$arList['PVZ'] as $cityCode => $arCityPVZ){
            if(array_key_exists($cityCode,self::$arList['CITY'])){
                $cityName = self::$arList['CITY'][$cityCode];
                $arList['PVZ'][$cityName] = array();
                foreach ($arCityPVZ as $pvzCode => $arPVZ){
                    $arList['PVZ'][$cityName][$pvzCode] = $arPVZ;
                }
            }
        }

        return $arList;
    }

    /**
     * Ger info from file or request
     */
    protected function loadPVZ()
    {
        if(!self::$arList) {
            if (self::isActual()) {
                self::$arList = json_decode(file_get_contents(self::getFilePath()),true);
            } else {
                $forseUpdate = self::updateList();
                if ($forseUpdate['SUCCESS']) {
                    self::$arList = $forseUpdate['DATA'];
                }
            }
        }
    }

    // sunc
    public static function updateList($requestType = 'sdek')
    {
        $getData = self::requestPVZ($requestType);
        if($getData['SUCCESS']){
            if(!file_put_contents(self::getFilePath(),json_encode($getData['DATA']))){
                $getData = array(
                    'SUCCESS' => false,
                    'ERROR'   => GetMessage('IPOLSDEK_SUNCPVZ_NOWRITE')
                );
            }
        }

        return $getData;
    }

    protected function isActual()
    {
        return (
            file_exists(self::getFilePath()) &&
            mktime() - filemtime(self::getFilePath()) < self::$cacheTime
        );
    }

    /**
     * @return string
     * where file should be
     */
    public static function getFilePath()
    {
        return $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/list.json";
    }

    /**
     * @return array
     * request to SDEK or backup
     */
    public static function requestPVZ($requestType = 'sdek')
    {
        $request = ($requestType === 'backup') ? self::getIPOLPVZ() : self::getSDEKPVZ();
        $arList = array('PVZ' => array(), 'CITY' => array(), 'REGIONS' => array(), 'CITYFULL' => array(), 'COUNTRIES' => array());

        $allowedCountries = array();
        $optionCountries  = \sdekHelper::getCountryOptions();
        $arDict = array(
            'rus' => 'RU',
            'blr' => 'BY',
            'kaz' => 'KZ'
        );

        foreach ($optionCountries as $countryCode => $setups){
            if(
                array_key_exists($countryCode,$arDict)      &&
                array_key_exists('act',$setups) &&
                $setups['act'] == 'Y'
            ){
                $allowedCountries []= $arDict[$countryCode];
            }
        }

        $arReturn = array(
            'SUCCESS' => false,
            'ERROR'   => false,
            'DATA'    => false
        );

        if($request['code'] == 200){
            if($request['result'] && (!array_key_exists('error',$request) || !$request['error'])) {
                $xml = simplexml_load_string($request['result']);
                foreach ($xml as $key => $val) {
                    if (in_array((string)$val['countryCodeIso'], $allowedCountries)) {
                        $cityCode = (string)$val['CityCode'];
                        $type = strtoupper((string)$val['Type']);
                        $city = (string)$val["City"];
                        $city = str_replace(Tools::getMessage('LANG_YO_S',true),Tools::getMessage('LANG_YE_S',true), $city);
                        if (strpos($city, '(') !== false)
                            $city = trim(substr($city, 0, strpos($city, '(')));
                        if (strpos($city, ',') !== false)
                            $city = trim(substr($city, 0, strpos($city, ',')));
                        $code = (string)$val["Code"];

                        $arList[$type][$cityCode][$code] = array(
                            'Name'     => (string)$val['Name'],
                            'WorkTime' => (string)$val['WorkTime'],
                            'Address'  => (string)$val['Address'],
                            'Phone'    => (string)$val['Phone'],
                            'Note'     => str_replace(array("\n", "\r"), '', nl2br((string)$val['Note'])),
                            'cX'       => (string)$val['coordX'],
                            'cY'       => (string)$val['coordY'],
                            'Dressing' => (string)$val['IsDressingRoom'],
                            'Cash'     => (string)$val['HaveCashless'],
                            'Station'  => (string)$val['NearestStation'],
                            'Site'     => (string)$val['Site'],
                            'Metro'    => (string)$val['MetroStation'],
                            'payNal'   => (string)$val['AllowedCod'],
                            'AddressComment' => (string)$val['AddressComment']
                        );
                        if ($val->WeightLimit) {
                            $arList[$type][$cityCode][$code]['WeightLim'] = array('MIN' => (float)$val->WeightLimit['WeightMin'], 'MAX' => (float)$val->WeightLimit['WeightMax']);
                        }

                        $arImgs = array();

                        foreach ($val->OfficeImage as $img) {
                            if (strstr($_tmpUrl = (string)$img['url'], 'http') === false) {
                                continue;
                            }
                            $arImgs[] = (string)$img['url'];
                        }

                        if (count($arImgs = array_filter($arImgs)))
                            $arList[$type][$cityCode][$code]['Picture'] = $arImgs;
                        if ($val->OfficeHowGo)
                            $arList[$type][$cityCode][$code]['Path'] = (string)$val->OfficeHowGo['url'];

                        if (!array_key_exists($cityCode, $arList['CITY'])) {
                            $arList['CITY'][$cityCode] = $city;
                            $arList['CITYFULL'][$cityCode] = (string)$val['CountryName'] . ' ' . (string)$val['RegionName'] . ' ' . $city;
                            $arList['REGIONS'][$cityCode] = implode(', ', array_filter(array((string)$val['RegionName'], (string)$val['CountryName'])));
                        }
                    }
                }
                krsort($arList['PVZ']);

                if (empty($arList['PVZ'])) {
                    $arReturn['ERROR'] = GetMessage('IPOLSDEK_SUNCPVZ_NODATA');
                } else {
                    $arReturn['SUCCESS'] = true;
                    $arReturn['DATA'] = $arList;
                }
            } elseif($request['error']){
                $arReturn['ERROR']   = $request['error'];
            }
        }
        else{
            $arReturn['ERROR']   = GetMessage('IPOLSDEK_FILE_UNBLUPDT').$request['code'].".";
        }

        return $arReturn;
    }

    protected static function getSDEKPVZ(){
        return \sdekHelper::sendToSDEK(false,'pvzlist','type=ALL');
    }

    public static function getIPOLPVZ(){
        $basicAuth = \sdekHelper::getBasicAuth();
        $data = \sdekOption::nativeReq('pvzSunc/ajax.php',array('account' => $basicAuth['ACCOUNT'],'secure' => $basicAuth['SECURE']));

        if($data['code'] == 200){
            $request = json_decode($data['result']);
            if($request->success){
                $data['result'] = $request->data;
            } else {
                $data['error'] = $request->error;
            }
        }

        return $data;
    }
}