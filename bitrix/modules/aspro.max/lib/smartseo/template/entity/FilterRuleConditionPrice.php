<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleConditionPrice extends Iblock\Template\Entity\Base
{
    private $iblockId = null;
    private $filterConditionId = null;
    private $filterRuleId = null;

    /** @var string  */
    private $condition = null;

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResultHandler */
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

    public function setIblockId($value)
    {
        $this->iblockId = $value;
    }

    public function setCondition($value)
    {
        $this->condition = $value;
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

        if (!$this->condition || !$this->iblockId) {
            return false;
        }

        $this->conditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResultHandler($this->iblockId, $this->condition);

        $allCatalogGroupPropertyFields = $this->conditionTreeResult->getCatalogGroupPropertyFields();

        foreach ($allCatalogGroupPropertyFields as $priceField) {
            if (!$this->fieldMap[$priceField['CATALOG_GROUP_NAME']]) {
                $this->fieldMap[strtolower($priceField['CATALOG_GROUP_NAME'])] = $priceField['CATALOG_GROUP_ID'];
            }

            $this->fields[$priceField['CATALOG_GROUP_ID']] = $priceField['VALUES'];
        }

        return is_array($this->fields);
    }
}
