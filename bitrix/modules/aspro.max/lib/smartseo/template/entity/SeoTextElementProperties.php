<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class SeoTextElementProperties extends Iblock\Template\Entity\Base
{
    const DISPLAY_SLICE = 1;

    private $iblockId = null;

    /** @var string  */
    private $condition = null;

    private $sectionIds = [];

    private $sectionMargins = [];

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResultHandler  */
    private $conditionTreeResult = null;

    public function __construct($seotextId)
    {
        parent::__construct($seotextId);
    }

    public function resolve($entity)
    {
        return parent::resolve($entity);
    }

    public function setFields(array $fields)
    {
        parent::setFields($fields);

        if (!is_array($this->fields)) {
            return;
        }
    }

    public function setIblockId($value)
    {
       $this->iblockId = $value;
    }

    public function setCondition($value)
    {
       $this->condition = $value;
    }

    public function setSectionIds(array $values)
    {
       $this->sectionIds = $values;
    }

    public function setSectionMargins(array $values)
    {
       $this->sectionMargins = $values;
    }

    protected function loadFromDatabase()
    {

        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        if (!$this->condition || !$this->iblockId) {
            if(!$this->loadDataBySeotext()) {
                return false;
            }
        }

        $this->conditionTreeResult = new Smartseo\Condition\ConditionResultHandler($this->iblockId, $this->condition, [
                new Smartseo\Condition\Controls\GroupBuildControls(),
                new Smartseo\Condition\Controls\IblockPropertyBuildControls($this->iblockId, [
                    'ONLY_PROPERTY_SMART_FILTER' => 'N',
                    'SHOW_PROPERTY_SKU' => 'N',
                  ])
            ]);

        $this->conditionTreeResult->setIncludeSubsection(true);
        $this->conditionTreeResult->setSectionMargins($this->getSectionMargins());

        $allPropertyFields = $this->conditionTreeResult->getAllPropertyFields();
        $elementPropertyValues = $this->conditionTreeResult->getElementPropertyValues();

        foreach ($allPropertyFields as $propertyField) {
            if (!$this->fieldMap[$propertyField['PROPERTY_CODE']]) {
                $this->fieldMap[strtolower($propertyField['PROPERTY_CODE'])] = $propertyField['PROPERTY_ID'];
            }

            $_displayValues = array_unique(
              array_map(function($item) use ($propertyField) {
                  $value = $item['PROPERTY_' . $propertyField['PROPERTY_ID']];
                  $displayValue = null;

                  switch ($propertyField['PROPERTY_TYPE']) {
                      case 'S' && $propertyField['USER_TYPE'] :
                          $displayValue = new Iblock\Template\Entity\ElementPropertyUserField($value, [
                              'ID' => $propertyField['PROPERTY_ID'],
                              'USER_TYPE' => $propertyField['USER_TYPE'],
                              'USER_TYPE_SETTINGS' => unserialize($propertyField['USER_TYPE_SETTINGS']),
                          ]);


                          break;

                      case 'E' :
                          $displayValue = new Iblock\Template\Entity\ElementPropertyElement($value);

                          break;

                      case 'L' :
                          $displayValue = new Iblock\Template\Entity\ElementPropertyEnum($value);

                          break;
                      default:
                          $displayValue = $value;

                          break;
                  }

                  return $displayValue;
              }, $elementPropertyValues)
            );

            $_displayValues = array_slice($_displayValues, 0, self::DISPLAY_SLICE);
            $this->fields[$propertyField['PROPERTY_ID']] = $_displayValues;
        }

        return is_array($this->fields);
    }

    protected function loadDataBySeotext()
    {
        if(!$this->id) {
            return;
        }

        $row = Smartseo\Models\SmartseoSeoTextTable::getRow([
              'select' => [
                  'ID',
                  'CONDITION_TREE',
                  'IBLOCK_ID',
              ],
              'filter' => [
                  '=ID' => $this->id
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
        ]);

        if (!$row) {
            return false;
        }

        $this->iblockId = $row['IBLOCK_ID'];
        $this->condition = $row['CONDITION_TREE'];

        return true;
    }

    protected function getSectionMargins()
    {
        if($this->sectionMargins) {
            return $this->sectionMargins;
        }

        if ($this->sectionIds) {
            return \Bitrix\Iblock\SectionTable::getList([
                  'select' => [
                      'LEFT_MARGIN',
                      'RIGHT_MARGIN'
                  ],
                  'filter' => [
                      '=ID' => $this->sectionIds,
                  ],
                  'cache' => [
                      'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  ],
              ])->fetchAll();
        }

        if ($this->id) {
            return Smartseo\Models\SmartseoSeoTextTable::getList([
                  'select' => [
                      'LEFT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.LEFT_MARGIN',
                      'RIGHT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.RIGHT_MARGIN',
                  ],
                  'filter' => [
                      '=ID' => $this->id
                  ],
                  'cache' => [
                      'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                      'cache_joins' => true,
                  ],
              ])->fetchAll();
        }

        return [];
    }

}
