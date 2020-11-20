<?
namespace Aspro\Max;
use	CMax as Solution,
	CMaxCache as Cache,
	CMaxCondition as Condition,
	\Aspro\Max\Property\CustomFilter,
	\Bitrix\Main\Web\Json;

class MarketingPopup{
	const ALL_USERS_GROUP_ID = 2;
	const IBLOCK_TYPE = 'aspro_max_adv';
	const IBLOCK_CODE = 'aspro_max_marketings';
	const PROPERTY_FILTER_CODE = 'PROPERTY_FILTER_SHOW';

	const SECTION_CODE_POPUP_TYPE_NORMAL = 'main';
	const SECTION_CODE_POPUP_TYPE_MODAL = 'modal';
	const SECTION_CODE_POPUP_TYPE = [
		'main' => 'NORMAL',
		'modal' => 'MODAL'
	];

	protected $siteId;
	protected $iblockId;
	protected $arParams;
	protected $arRules;

	protected $arItemSelect;

	public function __construct($arParams = array()){
		$this->setInfo($arParams);
	}

	public function __set($name, $value){
		switch($name){
			case 'arParams':
				$this->setInfo($value);
				break;
			case 'siteId':
				$this->siteId = $value;
				$this->iblockId = self::_getSiteIblockId($this->siteId);
				$this->arParams = array();
				break;
		}

		return $value;
	}

	public function __get($name){
		if(property_exists($this, $name))
			return $this->{$name};

		return null;
	}

	protected function _reset(){
		$this->arRules = $this->$siteId = $this->iblockId = false;
		$this->arParams = $this->arItemSelect = array();
	}

	public function setInfo($arParams = array()){
		$this->_reset();

		$this->siteId = defined('SITE_ID') ? SITE_ID : false;
		if($this->siteId == 'ru') //admin page
			$this->siteId = 's1';

		$this->iblockId = self::_getSiteIblockId($this->siteId);
		$this->arParams = $arParams && is_array($arParams) ? $arParams : array();

	}

	public function getRules(){
		if($this->arRules === false)
		{
			$arRules = array();

			if($this->iblockId)
			{
				$arRulesTmp = array();

				// get current user groups
				$arUserGroups = self::_getUserGroups();

				$arSelect = array(
					'ID',
					'IBLOCK_ID',
					'IBLOCK_SECTION_ID',
					'PROPERTY_PRIORITY',
					'PROPERTY_SORT',
					'PROPERTY_LAST_LEVEL_RULE',
					'PROPERTY_LAST_RULE',
					self::PROPERTY_FILTER_CODE,
					'PROPERTY_USER_GROUPS',
					'PROPERTY_LINK_REGION',
					'PROPERTY_DELAY_SHOW',
					'PROPERTY_LS_TIMEOUT',
					"PROPERTY_MODAL_TYPE",
					"PROPERTY_POSITION",
					"PROPERTY_HIDE_TITLE",

				);

				$arFilter = array(
					'IBLOCK_ID' => $this->iblockId,
					'ACTIVE' => 'Y',
					array(
						'LOGIC' => 'OR',
						array('PROPERTY_USER_GROUPS' => $arUserGroups),
						array('PROPERTY_USER_GROUPS' => false),
					),
				);

				// use region
				if($GLOBALS['arRegion'] && $GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y')
				{
					$arFilter[] = array(
						'LOGIC' => 'OR',
						array('PROPERTY_LINK_REGION' => $GLOBALS['arRegion']['ID']),
						array('PROPERTY_LINK_REGION' => false),
					);
				}

				// get all rules for current user groups in current region
				if($arRulesTmp = Cache::CIBLockElement_GetList(
					array(
						/*'property_PRIORITY' => 'DESC',
						'property_SORT' => 'ASC',*/
						'SORT' => 'ASC',
						'CACHE' => array(
							'MULTI' => 'N',
							'TAG' => Cache::GetIBlockCacheTag($this->iblockId),
							'GROUP' => array('ID'),
						)
					),
					$arFilter,
					false,
					false,
					$arSelect
				))
				{
					$arSectionsIds = $arSections = [];
					foreach($arRulesTmp as $arItemTmp)
					{
						if($arItemTmp['IBLOCK_SECTION_ID'])
							$arSectionsIds[$arItemTmp['IBLOCK_SECTION_ID']] = $arItemTmp['IBLOCK_SECTION_ID'];
					}

					if($arSectionsIds)
					{
						$arSections = Cache::CIBLockSection_GetList(
							array(
								'SORT' => 'ASC',
								'CACHE' => array(
									'MULTI' => 'N',
									'TAG' => Cache::GetIBlockCacheTag($this->iblockId),
									'GROUP' => array('ID'),
								)
							),
							['IBLOCK_ID' => $this->iblockId, 'ID' => $arSectionsIds],
							false,
							['ID', 'CODE']
						);
					}

					// get active by date without cache
					$arRulesIDs = array_column($arRulesTmp, 'ID');
					$dbRes = \CIBlockElement::GetList(
						array(),
						array(
							'ID' => $arRulesIDs,
							'IBLOCK_ID' => $this->iblockId,
							'ACTIVE_DATE' => 'Y',
						),
						false,
						false,
						array('ID')
					);
					$arRulesIDs = array();
					while($arRule = $dbRes->Fetch())
					{
						if($arRulesTmp[$arRule['ID']]['IBLOCK_SECTION_ID'])
						{
							$arRulesTmp[$arRule['ID']]['SECTION_CODE'] = $arSections[$arRulesTmp[$arRule['ID']]['IBLOCK_SECTION_ID']]['CODE'];
							$arRulesTmp[$arRule['ID']]['POPUP_TYPE'] = (self::SECTION_CODE_POPUP_TYPE[$arRulesTmp[$arRule['ID']]['SECTION_CODE']] ? self::SECTION_CODE_POPUP_TYPE[$arRulesTmp[$arRule['ID']]['SECTION_CODE']] : "NORMAL");
						}
						$arRulesIDs[] = $arRule['ID'];
					}

					if($arRulesIDs)
					{
						$obCache = new \CPHPCache();
						$cacheTime = 36000000;
						$cacheTime = 0;
						$cacheTag = Cache::GetIBlockCacheTag($this->iblockId);
						$cachePath = '/CMaxCache/iblock/CIBlockElement_GetList/'.$cacheTag.'/';
						$cacheID = 'CIBlockElement_GetList_'.$cacheTag.md5(serialize($arRulesIDs));
						if($obCache->InitCache($cacheTime, $cacheID, $cachePath))
						{
							$res = $obCache->GetVars();
							$arRulesTmp = $res['arRulesTmp'];
							$this->arItemSelect = $res['arItemSelect'];
						}
						else
						{
							$this->arItemSelect = array(
								'ID',
								'IBLOCK_ID',
							);

							/*echo "<pre>";
							echo "All items<br>";
							print_r($arRulesTmp);
							echo "</pre>";*/

							// collect parsed conditions from FILTER_SHOW property
							// collect product filds for select
							$cond = new Condition();
							foreach($arRulesTmp as $i => &$arRule)
							{
								if(in_array($arRule['ID'], $arRulesIDs))
								{
									$bBadProductsFilter = true;
									if(is_string($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']))
									{
										if($arRule[self::PROPERTY_FILTER_CODE.'_VALUE'])
										{
											$arTmpProductsFilter = Json::decode($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']);

											if(is_array($arTmpProductsFilter))
											{
												try
												{
													$arRule[self::PROPERTY_FILTER_CODE.'_VALUE'] =  $this->parseCondition($arTmpProductsFilter, $this->arParams);
													$bBadProductsFilter = false;
												}
												catch(\Exception $e)
												{
													$arRule[self::PROPERTY_FILTER_CODE.'_VALUE'] = false;
												}
											}
										}
										else
										{
											$bBadProductsFilter = false;
										}
									}
								}

								// remove bad rule
								// unset($arRulesTmp[$i]);
							}
							unset($arRule);

							if($cond->arItemSelect)
							{
								$this->arItemSelect = array_merge(
									$this->arItemSelect,
									$cond->arItemSelect
								);
							}
							unset($cond);

							$obCache->StartDataCache($cacheTime, $cacheID, $cachePath);
							if(strlen($cacheTag))
							{
								$GLOBALS['CACHE_MANAGER']->StartTagCache($cachePath);
								$GLOBALS['CACHE_MANAGER']->RegisterTag($cacheTag);
								$GLOBALS['CACHE_MANAGER']->EndTagCache();
							}

							$obCache->EndDataCache(array('arRulesTmp' => $arRulesTmp, 'arItemSelect' => $this->arItemSelect));
						}
					}

					// get some fields & properties of product
					if($arRulesTmp)
					{
						$arLastRule = $arLastLevelRule = array();
						foreach($arRulesTmp as $i => &$arRule)
						{
							if($this->_checkParsedCondition($arRule[self::PROPERTY_FILTER_CODE.'_VALUE']))
							{
								$arRules['ALL'][$arRule['ID']] = &$arRule;
							}
						}
						unset($arRule);
					}
				}
			}

			$this->arRules = $arRules;
		}

		return $arRules;
	}

	public function getItemsFilter($showPlace = ''){
		$arFilter = array();

		if($this->arRules){
			if($arRules = strlen($showPlace) ? $this->arRules[$showPlace] : $this->arRules['ALL']){
				$arFilter = array_column($arRules, 'PROPERTY_EXT_PRODUCTS_FILTER_VALUE');

				if(count($arFilter) > 1){
					$arFilter['LOGIC'] = 'OR';
				}
				else{
					$arFilter = reset($arFilter);
				}

				$arFilter = array(
					'LOGIC' => 'AND',
					(
						($propertyExtProductsFilterIblockId = CustomFilter::getSettingsIblockId('EXT_PRODUCTS_FILTER', $this->iblockId)) ?
							array('!ID' => $this->productId, 'IBLOCK_ID' => $propertyExtProductsFilterIblockId, 'ACTIVE' => 'Y') :
							array('!ID' => $this->productId, 'ACTIVE' => 'Y')
					),
					$arFilter
				);

				if($GLOBALS['arRegion'] && $GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' && $GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y'){
					$regionId = $GLOBALS['arRegion']['ID'];
					if($arSectionsIds = Solution::getSectionsIds_NotInRegion($this->iblockId, $regionId)){
						$arFilter['!IBLOCK_SECTION_ID'] = $arSectionsIds;
					}
				}
			}
		}

		return $arFilter;
	}

	public function getItems($showPlace = '', $sort = 'rand', $order = 'ASC'){
		if($arFilter = $this->getItemsFilter($showPlace)){
			$displayElementSlider = ($this->arParams['DISPLAY_ELEMENT_SLIDER'] ? $this->arParams['DISPLAY_ELEMENT_SLIDER'] : 10);

			$propertyExtProductsFilterIblockId = CustomFilter::getSettingsIblockId('EXT_PRODUCTS_FILTER', $this->iblockId);

			// echo '<pre>';
			// print_r($arFilter);
			// echo '</pre>';

			return Cache::CIBLockElement_GetList(
				array(
					$sort => $order,
					'CACHE' => array(
						'TIME' => ($this->arParams['CACHE_TYPE'] !== 'N' ? $this->arParams['CACHE_TIME'] : 0),
						'MULTI' => 'Y',
						'TAG' => Cache::GetIBlockCacheTag($propertyExtProductsFilterIblockId),
						'RESULT' => array('ID'),
					)
				),
				$arFilter,
				false,
				array('nTopCount' => $displayElementSlider),
				array(
					'ID',
					'IBLOCK_ID',
				)
			);
		}

		return false;
	}

	protected static function _getSiteIblockId($siteId){
		return Cache::$arIBlocks[$siteId][self::IBLOCK_TYPE][self::IBLOCK_CODE][0] ? Cache::$arIBlocks[$siteId][self::IBLOCK_TYPE][self::IBLOCK_CODE][0] : false;
	}

	protected static function _getUserGroups(){
		$arGroups = array();

		if(isset($GLOBALS['USER']) && $GLOBALS['USER']->IsAuthorized()){
			$resUserGroup = \Bitrix\Main\UserGroupTable::getList(
				array(
					'filter' => array(
						'USER_ID' => $GLOBALS['USER']->GetID(),
					),
					'select' => array('GROUP_ID'),
				)
			);
			while($arGroup = $resUserGroup->fetch()){
			   $arGroups[] = $arGroup['GROUP_ID'];
			}
		}

		$arGroups[] = self::ALL_USERS_GROUP_ID;

		return $arGroups;
	}

	public function parseCondition($condition, $params)
	{
		$result = array();

		if (!empty($condition) && is_array($condition))
		{
			if ($condition['CLASS_ID'] === 'CondGroup')
			{
				if (!empty($condition['CHILDREN']))
				{
					foreach ($condition['CHILDREN'] as $child)
					{
						$childResult = $this->parseCondition($child, $params);

						// is group
						if ($child['CLASS_ID'] === 'CondGroup')
						{
							$result[] = $childResult;
						}
						// same property names not overrides each other
						elseif (isset($result[key($childResult)]))
						{
							$fieldName = key($childResult);

							if (!isset($result['LOGIC']))
							{
								$result = array(
									'LOGIC' => $condition['DATA']['All'],
									array($fieldName => $result[$fieldName])
								);
							}

							$result[][$fieldName] = $childResult[$fieldName];
						}
						else
						{
							$result += $childResult;
						}
					}

					/*echo "<pre>";
					echo "Parse rule<br>";
					print_r($result);
					print_r($condition);
					echo "</pre>";*/

					if (!empty($result))
					{
						// $this->parsePropertyCondition($result, $condition, $params);

						if (count($result) > 1)
						{
							$result['LOGIC'] = $condition['DATA']['All'];
						}
					}
				}
			}
			else
			{
				$result += $this->parseConditionLevel($condition, $params);
			}
		}

		return $result;
	}

	protected function parseConditionLevel($condition, $params)
	{
		$arParsCondition = $result = array();

		/*echo "<pre>";
		echo "parseConditionLevel<br>";
		print_r($condition);
		echo "</pre>";*/

		if (!empty($condition) && is_array($condition))
		{
			$arParsCondition = $this->parseConditionName($condition);

			if ($arParsCondition)
			{
				$operator = $this->parseConditionOperator($condition);
				$value = $this->parseConditionValue($condition, $arParsCondition['NAME']);
				$result[$operator.$arParsCondition['NAME']] = array(
					'NAME' => $arParsCondition['NAME'],
					'OPERATOR' => $operator,
					'VALUE' => $value,
					'CONDITION_VALUE' => $arParsCondition['CONDITION_VALUE'],
					'TYPE' => $arParsCondition['TYPE'],
				);

				/*if(strpos($name, 'CondIBProp') === false){
					$this->arProductSelect[] = $name;
				}*/
			}
		}

		return $result;
	}

	protected function parseConditionName(array $condition)
	{
		$result = [];
		$conditionNameMap = array(
			'CondPage' => array(
				'TYPE' => 'STRING',
				'NAME' => 'PAGE',
				'CONDITION_VALUE' => $GLOBALS["APPLICATION"]->GetCurPage(),
			),
			'CondServerName' => array(
				'TYPE' => 'STRING',
				'NAME' => 'PAGE',
				'CONDITION_VALUE' => $_SERVER['SERVER_NAME'],
			),
		);

		if (isset($conditionNameMap[$condition['CLASS_ID']]))
		{
			$result = $conditionNameMap[$condition['CLASS_ID']];
		}

		return $result;
	}

	protected function parseConditionOperator($condition)
	{
		$operator = '';

		switch ($condition['DATA']['logic'])
		{
			case 'Equal':
				$operator = '==';
				break;
			case 'Not':
				$operator = '!';
				break;
			case 'Contain':
				$operator = '%';
				break;
			case 'NotCont':
				$operator = '!%';
				break;
			case 'Great':
				$operator = '>';
				break;
			case 'Less':
				$operator = '<';
				break;
			case 'EqGr':
				$operator = '>=';
				break;
			case 'EqLs':
				$operator = '<=';
				break;
		}

		return $operator;
	}

	protected function parseConditionValue($condition, $name)
	{
		$value = $condition['DATA']['value'];

		switch ($name)
		{
			case 'DATE_ACTIVE_FROM':
			case 'DATE_ACTIVE_TO':
			case 'DATE_CREATE':
			case 'TIMESTAMP_X':
				$value = ConvertTimeStamp($value, 'FULL');
				break;
		}

		return $value;
	}

	protected function parsePropertyCondition(array &$result, array $condition, $params)
	{
		static $arPropertiesCodes;

		if (!empty($result))
		{
			$subFilter = array();

			foreach ($result as $name => $value)
			{
				if (!empty($result[$name]) && is_array($result[$name]) && !isset($result[$name]['NAME']))
				{
					$this->parsePropertyCondition($result[$name], $condition, $params);
				}
				else
				{
					if (($ind = strpos($name, 'CondIBProp')) !== false)
					{
						list($prefix, $iblock, $propertyId) = explode(':', $name);

						$operator = $ind > 0 ? substr($prefix, 0, $ind) : '';

						$catalogInfo = \CCatalogSku::GetInfoByIBlock($iblock);

						if(!isset($arPropertiesCodes)){
							$arPropertiesCodes = array();
						}

						if(!array_key_exists($propertyId, $arPropertiesCodes)){
							$propCode = \CIBlockProperty::GetByID($propertyId, $iblock)->Fetch()['CODE'];
							$propCode = strtoupper($propCode);
							$arPropertiesCodes[$propertyId] = $propCode;
						}
						else{
							$propCode = $arPropertiesCodes[$propertyId];
						}

						if (
							$catalogInfo['CATALOG_TYPE'] != \CCatalogSku::TYPE_CATALOG
							&& $catalogInfo['IBLOCK_ID'] == $iblock
						)
						{
							$subFilter[$operator.'PROPERTY_'.$propCode] = $value;
							$subFilter[$operator.'PROPERTY_'.$propCode]['NAME'] = 'PROPERTY_'.$propCode;
							$subFilter[$operator.'PROPERTY_'.$propCode]['PROPERTY'] = $propCode;
						}
						else
						{
							$result[$operator.'PROPERTY_'.$propCode] = $value;
							$result[$operator.'PROPERTY_'.$propCode]['NAME'] = 'PROPERTY_'.$propCode;
							$result[$operator.'PROPERTY_'.$propCode]['PROPERTY'] = $propCode;
						}

						$this->arProductSelect[] = 'PROPERTY_'.$propCode;

						unset($result[$name]);
					}
				}
			}

			if (!empty($subFilter) && !empty($catalogInfo))
			{
				$offerPropFilter = array(
					'IBLOCK_ID' => $catalogInfo['IBLOCK_ID'],
					'ACTIVE_DATE' => 'Y',
					'ACTIVE' => 'Y'
				);

				if ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'Y')
				{
					$offerPropFilter['HIDE_NOT_AVAILABLE'] = 'Y';
				}
				elseif ($params['HIDE_NOT_AVAILABLE_OFFERS'] === 'L')
				{
					$offerPropFilter[] = array(
						'LOGIC' => 'OR',
						'CATALOG_AVAILABLE' => 'Y',
						'CATALOG_SUBSCRIBE' => 'Y'
					);
				}

				if (count($subFilter) > 1)
				{
					$subFilter['LOGIC'] = $condition['DATA']['All'];
					$subFilter = array($subFilter);
				}

				$result += $subFilter;
			}
		}
	}

	protected function _checkParsedCondition($arParsedCondition){
		if($arParsedCondition && is_array($arParsedCondition))
		{
			foreach($arParsedCondition as $key => $value)
			{
				if(!is_array($value))
					continue;

				if(is_numeric($key))
				{
					$arParsedCondition[$key] = $this->_checkParsedCondition($value);

				}
				else
				{
					/*echo "<pre>";
					echo "_checkParsedCondition<br>";
					print_r($key);
					print_r($value);
					echo "</pre>";*/

					switch($value['OPERATOR'])
					{
						case '==':
							if($value['TYPE'] == 'ARRAY' && is_array($value['VALUE']))
								$arParsedCondition[$key] = in_array($value['VALUE'], $value['CONDITION_VALUE']);
							else
								$arParsedCondition[$key] = ($value['CONDITION_VALUE'] == $value['VALUE']);
							break;
						case '!':
							if($value['TYPE'] == 'ARRAY' && is_array($value['VALUE']))
								$arParsedCondition[$key] = !in_array($value['VALUE'], $value['CONDITION_VALUE']);
							else
								$arParsedCondition[$key] = ($value['CONDITION_VALUE'] == $value['VALUE']);
							break;
						case '<':
							$arParsedCondition[$key] = isset($find) && $find < $value['VALUE'];
							break;
						case '<=':
							$arParsedCondition[$key] = isset($find) && $find <= $value['VALUE'];
							break;
						case '>':
							$arParsedCondition[$key] = isset($find) && $find > $value['VALUE'];
							break;
						case '>=':
							$arParsedCondition[$key] = isset($find) && $find >= $value['VALUE'];
							break;
						case '%':
							$arParsedCondition[$key] = (strpos($value['CONDITION_VALUE'], $value['VALUE']) !== false);
							break;
						case '!%':
							$arParsedCondition[$key] = (strpos($value['CONDITION_VALUE'], $value['VALUE']) === false);
							break;
					}
				}
				/*var_dump($arParsedCondition[$key]);
				print_r($arParsedCondition[$key]);*/

				if($arParsedCondition['LOGIC'] === 'AND' && $arParsedCondition[$key] == false)
				{
					$arParsedCondition = false;
					break;
				}

				if($arParsedCondition['LOGIC'] === 'OR' && $arParsedCondition[$key] == true)
				{
					$arParsedCondition = true;
					break;
				}

				if(!isset($arParsedCondition['LOGIC']))
				{
					if($arParsedCondition[$key] == true)
						$arParsedCondition = true;
					else
						$arParsedCondition = false;
				}
			}

			if(is_array($arParsedCondition))
			{
				if($arParsedCondition['LOGIC'] === 'OR')
					$arParsedCondition = false;
				else
					$arParsedCondition = true;
			}
		}

		return boolval($arParsedCondition);
	}
}
?>