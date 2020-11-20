<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleConditionProperty extends Iblock\Template\Entity\Base
{
    const DISPLAY_SLICE = 1;

    private $iblockId = null;
    private $filterConditionId = null;
    private $filterRuleId = null;

    /** @var string  */
    private $condition = null;

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResultHandler  */
    private $conditionTreeResult = null;

    public function __construct($filterConditionId)
    {
        parent::__construct($filterConditionId);
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

    public function setParams(array $params)
    {
        if ($params['IBLOCK_ID'] > 0) {
            $this->iblockId = $params['IBLOCK_ID'];
        }

        if ($params['FILTER_RULE_ID'] > 0) {
            $this->filterRuleId = $params['FILTER_RULE_ID'];
        }

        if ($params['FILTER_CONDITION_ID'] > 0) {
            $this->filterConditionId = $params['FILTER_CONDITION_ID'];
        }

        if ($params['CONDITION']) {
            $this->condition = $params['CONDITION'];
        }
    }

    protected function loadFromDatabase()
    {
        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        $this->loadRuleCondition();

        if (!$this->condition || !$this->iblockId || !$this->filterRuleId) {
            return false;
        }

        $this->conditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResultHandler($this->iblockId, $this->condition);
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

    protected function loadRuleCondition()
    {
        if(!$this->id) {
            return;
        }

        $row = Smartseo\Models\SmartseoFilterConditionTable::getRow([
              'select' => [
                  'ID',
                  'FILTER_RULE_ID',
                  'CONDITION_TREE',
                  'IBLOCK_ID' => 'FILTER_RULE.IBLOCK_ID',
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
            return;
        }

        $this->filterConditionId = $row['ID'];
        $this->filterRuleId = $row['FILTER_RULE_ID'];
        $this->iblockId = $row['IBLOCK_ID'];
        $this->condition = $row['CONDITION_TREE'];
    }

    protected function getSectionMargins()
    {
        if (!$this->filterRuleId) {
            return [];
        }

        $result = Smartseo\Models\SmartseoFilterRuleTable::getList([
              'select' => [
                  'LEFT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.LEFT_MARGIN',
                  'RIGHT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.RIGHT_MARGIN',
              ],
              'filter' => [
                  '=ID' => $this->filterRuleId
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
          ])->fetchAll();

        return $result;
    }

}
