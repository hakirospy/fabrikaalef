<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

use Aspro\Max\Smartseo\Condition,
    Bitrix\Highloadblock\HighloadBlockTable;

class PropertyUrlHandler extends AbstractUrlHandler
{

    const BT_COND_LOGIC_EGR = 'EqGr';           // >= (great or equal)
    const BT_COND_LOGIC_ELS = 'EqLs';           // <= (less or equal)
    const URL_TYPE_GENERATE_MERGE = 'MR';
    const URL_TYPE_GENERATE_COMBO = 'CM';
    const CACHE_TTL_ELEMENT_PROPERTY_VALUES = 3600;
    const CACHE_TTL_ENUM_VALUES = 3600;
    const CACHE_TTL_ELEMENTS = 3600;
    const LOGIC_AND = 'and';
    const LOGIC_OR = 'or';

    protected $logic = self::LOGIC_AND;
    protected $condition = null;
    protected $iblockId = null;
    protected $isStrictCompliance = true;
    protected $isIncludeSubsection = true;
    protected $isHighLoadBlockModule = false;
    protected $isFriendlyUrl = false;
    protected $globalFilterName = 'arrFilter';

    private $separatorValues = '-';
    private $prefixNumberMin = '';
    private $prefixNumberMax = 'to-';
    private $patternNativePriceNumberMin = 'price-%s-from-%s';
    private $patternNativePriceNumberMax = 'to-%s';

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResultHandler */
    private $conditionTreeResult = null;

    public function __construct(
      $iblockId,
      $condition,
      $type = true,
      $isIncludeSubsection = true,
      $isFriendlyUrl = false
      )
    {
        if ($type == self::URL_TYPE_GENERATE_MERGE) {
            $this->isStrictCompliance = true;
        }

        if ($type == self::URL_TYPE_GENERATE_COMBO) {
            $this->isStrictCompliance = false;
        }

        $this->isIncludeSubsection = $isIncludeSubsection;
        $this->iblockId = $iblockId;
        $this->condition = $condition;
        $this->isFriendlyUrl = $isFriendlyUrl;

        if (\Bitrix\Main\Loader::includeModule('highloadblock')) {
            $this->isHighLoadBlockModule = true;
        }

        $this->conditionTreeResult = new Condition\ConditionResultHandler($this->iblockId, $this->condition);

        $this->logic = $this->conditionTreeResult->getLogic();
    }

    public function setSettings(array $settings)
    {
        if ($settings['SEPARATOR_VALUE']) {
            $this->separatorValues = $settings['SEPARATOR_VALUE'];
        }

        if ($settings['PREFIX_NUMBER_MIN']) {
            $this->prefixNumberMin = $settings['PREFIX_NUMBER_MIN'];
        }

        if ($settings['PREFIX_NUMBER_MAX']) {
            $this->prefixNumberMax = $settings['PREFIX_NUMBER_MAX'];
        }

        if ($settings['SMARTFILTER_FILTER_NAME']) {
            $this->globalFilterName = $settings['SMARTFILTER_FILTER_NAME'];
        }
    }

    public function getPropertyReplacements()
    {
        return [
            '#PROPERTY_ID#' => 'PROPERTY_ID',
            '#PROPERTY_CODE#' => 'PROPERTY_CODE',
            '#PROPERTY_VALUE#' => 'VALUES',
        ];
    }

    public function getPriceReplacements()
    {
        return [
            '#PRICE_ID#' => 'PROPERTY_ID',
            '#PRICE_CODE#' => 'PROPERTY_CODE',
            '#PRICE_VALUE#' => 'VALUES',
        ];
    }

    public function getPropertyTokens()
    {
        return array_keys($this->getPropertyReplacements());
    }

    public function getPriceTokens()
    {
        return array_keys($this->getPriceReplacements());
    }

    public function getSmartFilterTokens()
    {
        return [
            '#SMART_FILTER_PATH#'
        ];
    }

    public function validateInitialParams()
    {
        if (!$this->condition) {
            $this->addError('PropertyUrlHandler: Requered params CONDITION not value or not found');

            return false;
        }

        if (!$this->iblockId) {
            $this->addError('PropertyUrlHandler: Requered params IBLOCK ID not value or not found');

            return false;
        }

        return true;
    }

    public function generateResult(&$results)
    {
        $newResult = [];

        $i = 0;

        foreach ($results as $result) {
            if (!$result['PARAMS']['SECTION']) {
                continue;
            }

            $resultRows = $this->getResultValues($result['PARAMS']['SECTION']);

            if (!$resultRows) {
                continue;
            }

            foreach ($resultRows as $properties) {
                $newResult[$i]['URL_PAGE'] = $this->getReplacedUrl($result['URL_PAGE'], $properties);

                if($this->isFriendlyUrl) {
                    $newResult[$i]['URL_SMART_FILTER'] = $this->getReplacedUrlForBitrix($result['URL_SMART_FILTER'], $properties);
                } else {
                     $newResult[$i]['URL_SMART_FILTER'] = $this->getReplacedNotFriendlyUrlForBitrix($result['URL_SECTION'], $properties);
                }
                $newResult[$i]['PARAMS'] = $result['PARAMS'];
                $newResult[$i]['PARAMS']['PROPERTIES'] = $properties;

                $i++;
            }
        }

        $results = $newResult;
    }

    protected function getResultValues(array $section)
    {
        $elementPropertyValues = $this->getElementPropertyList($section['LEFT_MARGIN'], $section['RIGHT_MARGIN']);
        $countElementPropertyValues = count($elementPropertyValues);

        if (!$elementPropertyValues) {
            return [];
        }

        $result = [];
        $resultCatalogGroup = [];

        $i = 0;

        if ($this->getCatalogGroupPropertyFields()) {
            foreach ($this->getCatalogGroupPropertyFields() as $catalogGroupField) {
                $resultCatalogGroup[] = $this->getModifiedCatalogGroupProperty($catalogGroupField);
            }
        }

        if ($this->isStrictCompliance == true) {

            if ($resultCatalogGroup && $elementPropertyValues) {
                $result[$i] = $resultCatalogGroup;
            }

            foreach ($this->getAllPropertyFields() as $propertyField) {
                $_values = array_unique(
                  array_map(function($item) use ($propertyField) {
                      return $item['PROPERTY_' . $propertyField['PROPERTY_ID']];
                  }, $elementPropertyValues)
                );

                if (!$_values) {
                    continue;
                }

                $result[$i][] = $this->getModifiedProperty($propertyField, $_values);

                if ($this->logic == self::LOGIC_OR) {
                    $i++;
                }
            }
        }

        $i = 0;

        if ($this->isStrictCompliance == false) {
            foreach ($elementPropertyValues as $rowValue) {
                if ($resultCatalogGroup) {
                    $result[$i] = $resultCatalogGroup;
                }

                foreach ($this->getAllPropertyFields() as $propertyField) {
                    $_values = $rowValue['PROPERTY_' . $propertyField['PROPERTY_ID']];

                    $result[$i][] = $this->getModifiedProperty($propertyField, [$_values]);

                    if ($this->logic == self::LOGIC_OR) {
                        $i++;
                    }
                }

                if ($this->logic == self::LOGIC_AND) {
                    $i++;
                }
            }
        }

        return $result;
    }

    protected function getModifiedProperty($propertyField, array $values)
    {
        $result = [
            'PROPERTY_ID' => $propertyField['PROPERTY_ID'],
            'PROPERTY_CODE' => $propertyField['PROPERTY_CODE'],
            'PROPERTY_TYPE' => $propertyField['PROPERTY_TYPE'],
            'PROPERTY_NAME' => $propertyField['PROPERTY_NAME'],
            'PROPERTY_DISPLAY_TYPE' => $propertyField['PROPERTY_DISPLAY_TYPE'],
            'LINK_IBLOCK_ID' => (int) $propertyField['LINK_IBLOCK_ID'],
        ];

        switch ($propertyField['PROPERTY_TYPE']) {
            case 'N' :
                $_logics = array_keys($propertyField['CONDITIONS']);
                $min = null;
                $max = null;

                if (in_array(self::BT_COND_LOGIC_EGR, $_logics)) {
                    $min = min($values);
                }

                if (in_array(self::BT_COND_LOGIC_ELS, $_logics)) {
                    $max = max($values);
                }

                $result['_VALUES'] = array_filter([
                    'MIN' => $min,
                    'MAX' => $max,
                ]);

                $result['VALUES'] = $this->getModifiedNumberValues($result['_VALUES']);
                break;

            case 'S' && $propertyField['USER_TYPE'] == 'directory' && $this->isHighLoadBlockModule :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedDirectoryValues($values, $propertyField['USER_TYPE_SETTINGS']);

                break;
            case 'S' :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedStringValues($values);

                break;
            case 'L' :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedEnumValues($values);

                break;
            case 'E' :
                $result['_VALUES'] = $values;

                if ($propertyField['LINK_IBLOCK_ID']) {
                    $result['VALUES'] = $this->getModifiedElementValues($values, $propertyField['LINK_IBLOCK_ID']);
                }

                break;
            default:
                break;
        }

        return $result;
    }

    protected function getModifiedCatalogGroupProperty($catalogGroupField)
    {
        $result = [
            'PROPERTY_ID' => $catalogGroupField['CATALOG_GROUP_ID'],
            'PROPERTY_NAME' => $catalogGroupField['CATALOG_GROUP_NAME'],
            'PROPERTY_CODE' => $catalogGroupField['CATALOG_GROUP_NAME'],
            'PROPERTY_TYPE' => 'PRICE',
        ];

        $_logics = array_keys($catalogGroupField['CONDITIONS']);
        $min = null;
        $max = null;

        if (in_array(self::BT_COND_LOGIC_EGR, $_logics)) {
            $min = min($catalogGroupField['VALUES']);
        }

        if (in_array(self::BT_COND_LOGIC_ELS, $_logics)) {
            $max = max($catalogGroupField['VALUES']);
        }

        $result['_VALUES'] = array_filter([
            'MIN' => $min,
            'MAX' => $max,
        ]);

        $result['VALUES'] = [
            'NEW' => array_filter([
                $result['_VALUES']['MIN'] ? $this->prefixNumberMin . $result['_VALUES']['MIN'] : null,
                $result['_VALUES']['MAX'] ? $this->prefixNumberMax . $result['_VALUES']['MAX'] : null,
            ]),
            'DISPLAY' => $result['_VALUES'],
        ];

        if($this->isFriendlyUrl) {
            $result['VALUES']['ORIGIN'] = array_filter([
                $result['_VALUES']['MIN']
                    ? sprintf($this->patternNativePriceNumberMin, $catalogGroupField['CATALOG_GROUP_NAME'], $result['_VALUES']['MIN'])
                    : null,
                $result['_VALUES']['MAX']
                    ? sprintf($this->patternNativePriceNumberMax, $result['_VALUES']['MAX'])
                    : null,
            ]);
        } else {
            $result['VALUES']['ORIGIN'] = $result['_VALUES'];
        }

        return $result;
    }

    protected function getModifiedNumberValues(array $values)
    {
        $result = [];

        $result = [
            'NEW' => array_filter([
                $values['MIN'] ? $this->prefixNumberMin . $values['MIN'] : null,
                $values['MAX'] ? $this->prefixNumberMax . $values['MAX'] : null,
            ]),
            'DISPLAY' => $values,
        ];

        if($this->isFriendlyUrl) {
            $result['ORIGIN'] = array_filter([
                $values['MIN'] ? 'from-' . $values['MIN'] : null,
                $values['MAX'] ? 'to-' . $values['MAX'] : null,
            ]);
        } else {
             $result['ORIGIN'] = array_filter([
                $values['MIN'] ? $values['MIN'] : null,
                $values['MAX'] ? $values['MAX'] : null,
            ]);
        }

        return $result;
    }

    protected function getModifiedStringValues(array $values)
    {
        $newValues = [];
        $originValues = [];
        foreach ($values as $value) {
            $value = trim($value);
            $newValues[] = \Cutil::translit($value, 'ru', [
                  'replace_space' => '-',
                  'replace_other' => '-',
            ]);
            $originValues[] = $value;
        }

        natcasesort($newValues);
        natcasesort($originValues);
        natcasesort($originValues);

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $originValues,
        ];
    }

    protected function getModifiedEnumValues(array $values)
    {
        $rows = \Bitrix\Iblock\PropertyEnumerationTable::getList([
              'select' => [
                  'ID',
                  'VALUE',
                  'XML_ID',
              ],
              'filter' => [
                  '=ID' => $values
              ],
              'order' => [
                  'SORT',
                  'VALUE',
              ],
              'cache' => [
                  'ttl' => self::CACHE_TTL_ENUM_VALUES,
              ]
          ])->fetchAll();

        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = \Cutil::translit($row['VALUE'], 'ru', [
                  'replace_space' => '-',
                  'replace_other' => '-',
            ]);
            $originValues[] = $this->isFriendlyUrl ? $row['XML_ID'] : $row['ID'];
            $displayValues[] = $row['VALUE'];
        }

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getModifiedElementValues(array $values, $iblockId)
    {
        $rows = \Bitrix\Iblock\ElementTable::getList([
              'select' => [
                  'ID',
                  'CODE',
                  'NAME',
              ],
              'filter' => [
                  '=ID' => $values,
                  'IBLOCK_ID' => $iblockId,
              ],
              'order' => [
                  'SORT',
                  'NAME',
                  'CODE',
              ],
              'cache' => [
                  'ttl' => self::CACHE_TTL_ELEMENTS,
              ]
          ])->fetchAll();

        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = \Cutil::translit($row['CODE'] ?: $row['NAME'], 'ru', [
                  'replace_space' => '-',
                  'replace_other' => '-',
            ]);

            if($this->isFriendlyUrl) {
                $originValues[] = $row['CODE'] ?: $row['NAME'];
            } else {
                $originValues[] = $row['ID'];
            }

            $displayValues[] = $row['NAME'];
        }

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getModifiedDirectoryValues(array $values, $userTypeSettings)
    {
        if (!$userTypeSettings) {
            return [];
        }

        $settings = unserialize($userTypeSettings);
        $tableName = $settings['TABLE_NAME'];

        $hlblock = HighloadBlockTable::getList([
              'filter' => [
                  '=TABLE_NAME' => $tableName
              ]
          ])->fetch();

        if (!$hlblock) {
            return [];
        }

        $hlClassName = HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $rows = $hlClassName::getList([
              'select' => [
                  'ID',
                  'UF_NAME',
                  'UF_XML_ID',
              ],
              'filter' => [
                  'UF_XML_ID' => $values
              ],
              'order' => [
                  'UF_SORT',
                  'UF_NAME',
                  'UF_XML_ID'
              ]
          ])->fetchAll();


        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = $row['UF_XML_ID'];
            $originValues[] = $row['UF_XML_ID'];
            $displayValues[] = $row['UF_NAME'];
        }

        //natcasesort($newValues);

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getPropertyFields()
    {
        $properties = $this->conditionTreeResult->getPropertyFields();

        usort($properties, function($a, $b) {
            return ($a['PROPERTY_SORT'] > $b['PROPERTY_SORT']);
        });

        return $properties;
    }

    protected function getSkuPropertyFields()
    {
        $skuProperties = $this->conditionTreeResult->getSkuPropertyFields();

        usort($skuProperties, function($a, $b) {
            return ($a['PROPERTY_SORT'] > $b['PROPERTY_SORT']);
        });

        return $skuProperties;
    }

    protected function getAllPropertyFields()
    {
        return array_merge($this->getPropertyFields(), $this->getSkuPropertyFields());
    }

    protected function getCatalogGroupPropertyFields()
    {
        return $this->conditionTreeResult->getCatalogGroupPropertyFields();
    }

    private function getElementPropertyList($sectionLeftMargin, $sectionRightMargin)
    {
        $this->conditionTreeResult->setSectionMargin($sectionLeftMargin, $sectionRightMargin);
        $this->conditionTreeResult->setIncludeSubsection($this->isIncludeSubsection);

        $result = $this->conditionTreeResult->getElementPropertyValues();

        return $result;
    }

    protected function getReplacedUrl($url, $data)
    {
        $propertyReplateTokens = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getPropertyTokens());

        $priceReplateTokens = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getPriceTokens());

        preg_match_all('/\{(.+?)\}/', $url, $matches);

        if (!$matches) {
            return $url;
        }

        $urlReplacementPatterns = $matches[0];
        $tokenReplacementPatterns = $matches[1];

        $result = null;

        foreach ($data as $property) {
            if (!$property['VALUES']['NEW']) {
                continue;
            }

            if ($property['PROPERTY_TYPE'] == 'PRICE') {
                $_replateTokens = $priceReplateTokens;
            } else {
                $_replateTokens = $propertyReplateTokens;
            }

            $i = 0;
            foreach ($tokenReplacementPatterns as $template) {
                $_replaceResultStr = preg_replace($_replateTokens, [
                    $property['PROPERTY_ID'],
                    $property['PROPERTY_CODE'],
                    implode($this->separatorValues, $property['VALUES']['NEW'])
                    ], $template
                );

                if($_replaceResultStr != $template) {
                    $result[$i] .= mb_strtolower($_replaceResultStr);
                }

                $i++;
            }
        }

        $i = 0;
        foreach ($urlReplacementPatterns as $template) {
            $url = str_replace(['{', '}'], '', preg_replace($template, $result[$i], $url));

            $i++;
        }

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

    protected function getReplacedUrlForBitrix($urlSmartFilterTemplate, $data)
    {
        $patterns = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getSmartFilterTokens());

        $result = '';

        $replacements = [];

        $_temp = [];
        foreach ($data as $property) {
            if (!$property['VALUES']['ORIGIN']) {
                continue;
            }

            $separator = '-is-';
            $separatorValues = '-or-';

            if ($property['PROPERTY_TYPE'] == 'N') {
                $separator = '-';
                $separatorValues = '-';
            }

            if ($property['PROPERTY_TYPE'] == 'PRICE') {
                $property['PROPERTY_CODE'] = '';
                $separator = '';
                $separatorValues = '-';
            }

            $_temp[] = mb_strtolower($property['PROPERTY_CODE'] . $separator . implode($separatorValues, $property['VALUES']['ORIGIN']));
        }

        $replacements[] = implode('/', $_temp);

        $url = preg_replace($patterns, $replacements, $urlSmartFilterTemplate);

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

    protected function getReplacedNotFriendlyUrlForBitrix($urlSection, $data)
    {
        $filterParams = [];
        $filterParams['set_filter'] = 'y';
        $filterPriceParams = [];

        foreach ($data as $property) {
             if($property['PROPERTY_TYPE'] == 'PRICE') {
                if($property['VALUES']['ORIGIN']['MIN']) {
                    $_code = $this->globalFilterName . '_P' . $property['PROPERTY_ID']. '_MIN';
                    $filterPriceParams[$_code] = $property['VALUES']['ORIGIN']['MIN'];
                }

                if($property['VALUES']['ORIGIN']['MAX']) {
                    $_code = $this->globalFilterName . '_P' . $property['PROPERTY_ID']. '_MAX';
                    $filterPriceParams[$_code] = $property['VALUES']['ORIGIN']['MAX'];
                }

                continue;
            }

            if ($property['PROPERTY_TYPE'] == 'N') {
                if ($property['VALUES']['ORIGIN']['MIN']) {
                    $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'] . '_MIN';
                    $filterParams[$_code] = $property['VALUES']['ORIGIN']['MIN'];
                }

                if ($property['VALUES']['ORIGIN']['MAX']) {
                    $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'] . '_MAX';
                    $filterParams[$_code] = $property['VALUES']['ORIGIN']['MAX'];
                }

                continue;
            }

            foreach ($property['VALUES']['ORIGIN'] as $value) {
                $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'];
                $value = abs(crc32(htmlspecialcharsbx($value)));

                if(in_array($property['PROPERTY_DISPLAY_TYPE'], ['F', 'G', 'H'])) {
                    $filterParams[$_code . '_'  . $value] = 'Y';
                } else {
                    $filterParams[$_code] = $value;
                }
            }
        }

        $url = \CHTTP::urlAddParams($urlSection, array_merge($filterParams, $filterPriceParams), [
            'skip_empty' => true,
            'encode' => true,
        ]);

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

}
