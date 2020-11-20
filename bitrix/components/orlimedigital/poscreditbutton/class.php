<?php

class PosCreditOneClickButton extends CBitrixComponent
{

    protected function checkModules()
    {
        return true;
    }

    public function executeComponent()
    {
        $this->includeComponentLang('class.php');

        if ($this->checkModules()) {
            $this->arResult = array();
            $this->initData();
            $this->initParams();
            $this->initOrder();
            $this->includeComponentTemplate();
        }
    }

    protected function initData()
    {
        $arData = array(
            'ACTION' => 'CREATE_ORDER',
            'ORDER' => array(
                'SITE_ID' => SITE_ID,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'PRODUCT_ID' => $this->arParams['ELEMENT_ID'],
                'PAY_SYSTEM_ID' => $this->arParams['DEFAULT_PAYMENT'],
            ),
        );

        if (!strlen($this->arParams['DEFAULT_CURRENCY'])) {
            $arData['ORDER']['CURRENCY'] = COption::GetOptionString('sale', 'default_currency', 'RUB');
        } else {
            $arData['ORDER']['CURRENCY'] = $this->arParams['DEFAULT_CURRENCY'];
        }

        $this->arResult['DATA'] = $arData;
    }

    protected function initParams()
    {
        global $USER;

        $arPBParams = array();

        if($USER->IsAuthorized()) {
            $arPBParams['fullName'] = $USER->GetFullName();
            $rsUser = CUser::GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            $arPBParams["phone"] = $arUser["PERSONAL_PHONE"];

        }

        $this->arResult['PARAMS'] = $arPBParams;
    }

    protected function initOrder()
    {
        $res = CCatalogGroup::GetList(array('SORT' => 'ASC'));
        while ($arRes = $res->Fetch()) {
            $arPrices[$arRes['NAME']] = '[' . $arRes['ID'] . '] ' . $arRes['NAME'] . ' ' . ($arRes['BASE'] == 'Y' ? GetMessage('BASE_PRICE') : '');
        }

        $arItems = array();
        $priceID = $this->arParams['PRICE_ID'];
        $arCurrencyParams = array('CURRENCY_ID' => $this->arParams['DEFAULT_CURRENCY']);
        $priceType = CIBlockPriceTools::GetCatalogPrices($this->arParams['IBLOCK_ID'], array($priceID));
        $cgroup = $priceType[$priceID]['SELECT'];

        $result = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>$this->arParams['IBLOCK_ID'], 'ID'=>$this->arParams['ELEMENT_ID']), false, false, array('*', $cgroup));
        $arProduct = $result->GetNextElement();

        if(is_object($arProduct)) {
            $fields = $arProduct->GetFields();
            $price = CIBlockPriceTools::GetItemPrices($this->arParams['IBLOCK_ID'], $priceType, $fields, true, $arCurrencyParams);
            $quantity = 1;

            $arItems[] = array(
                'model' => $fields['NAME'],
                'price' => $price[$priceID]['DISCOUNT_VALUE_VAT'],
                'quantity' => $quantity
            );

            $this->arResult['DATA']['ORDER']['NAME'] = $fields['NAME'];
            $this->arResult['DATA']['ORDER']['PRICE'] = $price[$priceID]['DISCOUNT_VALUE_VAT'];

            $this->arResult['DATA']['ORDER']['PERSONTYPE'] = $this->arParams['DEFAULT_PERSON_TYPE'];

            $this->arResult['DATA']['ORDER']['QUANTITY'] = $quantity;

            $this->arResult['PARAMS']['manualOrderInput'] = false;
            $this->arResult['PARAMS']['order'] = $arItems;

        } else {
            $this->arResult['PARAMS']['manualOrderInput'] = true;
            $this->arResult['PARAMS']['payAmount'] = 0;
        }
    }
}