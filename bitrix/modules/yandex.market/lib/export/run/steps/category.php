<?php

namespace Yandex\Market\Export\Run\Steps;

use Bitrix\Main;
use Bitrix\Iblock;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Category extends Base
{
	public function getName()
	{
		return 'category';
	}

	public function run($action, $offset = null)
	{
		$result = new Market\Result\Step();
		$context = $this->getContext();
		$sectionList = $this->getSectionList($context);
		$tagValuesList = $this->buildTagValuesList([], $sectionList, $context);

		$this->setRunAction($action);
		$this->extendData($tagValuesList, $sectionList, $context);
		$this->writeData($tagValuesList, $sectionList, $context);

		return $result;
	}

	public function getFormatTag(Market\Export\Xml\Format\Reference\Base $format, $type = null)
	{
		return $format->getCategory();
	}

	public function getFormatTagParentName(Market\Export\Xml\Format\Reference\Base $format)
	{
		return $format->getCategoryParentName();
	}

	protected function getStorageDataClass()
	{
		return Market\Export\Run\Storage\CategoryTable::getClassName();
	}

	protected function getDataLogEntityType()
	{
		return Market\Logger\Table::ENTITY_TYPE_EXPORT_RUN_CATEGORY;
	}

	protected function getIgnoredTypeChanges()
	{
		return [
			Market\Export\Run\Manager::ENTITY_TYPE_CURRENCY => true,
			Market\Export\Run\Manager::ENTITY_TYPE_PROMO => true,
			Market\Export\Run\Manager::ENTITY_TYPE_GIFT => true,
		];
	}

	protected function getSectionList($context)
	{
		$usedFieldTypes = $this->getUsedSourceFieldTypes();
		$hasFewTypes = (count($usedFieldTypes) > 1);
		$result = [];

		foreach ($usedFieldTypes as $fieldType => $fieldConfig)
		{
			$usedSectionIds = $this->getUsedSectionIds($context, $hasFewTypes ? $fieldConfig['IBLOCK_LINK_ID'] : null);

			if (!empty($usedSectionIds))
			{
				$typeSectionList = null;

				if ($fieldConfig['CONFLICT'] !== null)
				{
					$usedSectionIds = $this->revertConflictForSectionIdList($usedSectionIds, $fieldConfig['CONFLICT']);
				}

				switch ($fieldType)
				{
					case Market\Export\Entity\Data::TYPE_SERVICE_CATEGORY:
						$typeSectionList = $this->getSectionListFromService($usedSectionIds, $context);
					break;

					default:
						$typeSectionList = $this->getSectionListFromIblock($usedSectionIds, $context);
						$typeSectionList = $this->extendSectionListFromIblock($typeSectionList, $fieldConfig['IBLOCK_CONTEXT'], $fieldConfig['IBLOCK_SETTINGS']);
					break;
				}

				if (!empty($typeSectionList))
				{
					if ($fieldConfig['CONFLICT'] !== null)
					{
						$typeSectionList = $this->applyConflictForSectionList($typeSectionList, $fieldConfig['CONFLICT']);
					}

					if (empty($result))
					{
						$result = $typeSectionList;
					}
					else
					{
						$result += $typeSectionList;
					}
				}
			}
		}

		return $result;
	}

	protected function getSectionListFromService($usedSectionIds, $context)
	{
		$result = [];
		$usedSectionsMap = array_flip($usedSectionIds);
		$serviceCategoryList = Market\Service\Data\Category::getList();
		$currentTree = [];
		$currentTreeDepth = 0;

		foreach ($serviceCategoryList as $serviceCategoryKey => $serviceCategory)
		{
			if ($serviceCategory['depth'] < $currentTreeDepth)
			{
				array_splice($currentTree, $serviceCategory['depth']);
			}

			$currentTree[$serviceCategory['depth']] = $serviceCategoryKey;
			$currentTreeDepth = $serviceCategory['depth'];

			if (isset($usedSectionsMap[$serviceCategory['id']]))
			{
				foreach ($currentTree as $treeCategoryKey)
				{
					$treeCategory = $serviceCategoryList[$treeCategoryKey];

					if (!isset($result[$treeCategory['id']]))
					{
						$result[$treeCategory['id']] = [
							'ID' => $treeCategory['id'],
							'PARENT_ID' => $treeCategory['parentId'],
							'NAME' => Market\Service\Data\Category::getTitle($treeCategory['id']),
						];
					}
				}
			}
		}

		return $result;
	}

	protected function getSectionListFromIblock($usedSectionIds, $context)
	{
		$result = [];

		if (Main\Loader::includeModule('iblock'))
		{
			$usedSectionsMap = array_flip($usedSectionIds);

			// find used sections

			$querySections = \CIBlockSection::GetList(
				[],
				[ 'ID' => $usedSectionIds, 'CHECK_PERMISSIONS' => 'N' ],
				false,
				[ 'IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'NAME', 'LEFT_MARGIN']
			);

			while ($section = $querySections->Fetch())
			{
				$sectionData = [
					'IBLOCK_ID' => (int)$section['IBLOCK_ID'],
					'ID' => (int)$section['ID'],
					'NAME' => trim($section['NAME']),
					'LEFT_MARGIN' => (int)$section['LEFT_MARGIN']
				];
				$parentId = (int)$section['IBLOCK_SECTION_ID'];

				if ($parentId <= 0)
				{
					// hasn't parent
				}
				else if (isset($usedSectionsMap[$parentId])) // will selected
				{
					$sectionData['PARENT_ID'] = $parentId;
				}
				else if (isset($result[$parentId])) // already selected
				{
					$sectionData['PARENT_ID'] = $parentId;
				}
				else // get chain
				{
					$queryParents = \CIBlockSection::GetNavChain($section['IBLOCK_ID'], $section['ID'], [ 'IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'NAME', 'LEFT_MARGIN' ]);

					while ($parent = $queryParents->Fetch())
					{
						$parentData = [
							'IBLOCK_ID' => (int)$parent['IBLOCK_ID'],
							'ID' => (int)$parent['ID'],
							'NAME' => trim($parent['NAME']),
							'LEFT_MARGIN' => (int)$parent['LEFT_MARGIN']
						];

						if ($parent['IBLOCK_SECTION_ID'] > 0)
						{
							$parentData['PARENT_ID'] = (int)$parent['IBLOCK_SECTION_ID'];
						}

						if ($parentData['ID'] !== $sectionData['ID'])
						{
							$result[$parentData['ID']] = $parentData;
						}
						else if (isset($parentData['PARENT_ID']))
						{
							$sectionData['PARENT_ID'] = $parentData['PARENT_ID'];
						}
					}
				}

				$result[$section['ID']] = $sectionData;
			}

			uasort($result, function($a, $b) {
				if ($a['LEFT_MARGIN'] === $b['LEFT_MARGIN']) { return 0; }

				return ($a['LEFT_MARGIN'] < $b['LEFT_MARGIN'] ? -1 : 1);
			});
		}

		return $result;
	}

	protected function extendSectionListFromIblock($sectionList, $contextList, $allSettings)
	{
		$result = $sectionList;
		$sectionsByIblock = $this->splitSectionListByIblock($sectionList);

		foreach ($sectionsByIblock as $iblockId => $iblockSectionIds)
		{
			if (!isset($allSettings[$iblockId]) || !isset($contextList[$iblockId])) { continue; }

			$settings = $allSettings[$iblockId];
			$context = $contextList[$iblockId];

			// name from additional fields

			if (!empty($settings['NAME_FIELD']) && $settings['NAME_FIELD'] !== 'NAME')
			{
				$iblockSectionMap = array_flip($iblockSectionIds);
				$iblockSections = array_intersect_key($sectionList, $iblockSectionMap);
				$fieldMap = [
					'NAME' => $settings['NAME_FIELD'],
				];

				$values = $this->loadSectionValuesFromIblock($iblockId, $iblockSections, array_values($fieldMap), $context);
				$result = $this->applySectionListOverrides($result, $values, $fieldMap);
			}

			// crumbs

			if (isset($settings['USE_BREADCRUMBS']) && (string)$settings['USE_BREADCRUMBS'] === '1')
			{
				$crumbs = $this->getSectionCrumbsFromIblock($iblockId, $context);
				$result = $this->appendSectionCrumbs($result, $crumbs);
			}
		}

		return $result;
	}

	protected function splitSectionListByIblock($sectionList)
	{
		$result = [];

		foreach ($sectionList as $section)
		{
			$iblockId = (int)$section['IBLOCK_ID'];

			if (!isset($result[$iblockId]))
			{
				$result[$iblockId] = [];
			}

			$result[$iblockId][] = $section['ID'];
		}

		return $result;
	}

	protected function loadSectionValuesFromIblock($iblockId, $sectionList, $select, $context)
	{
		$source = Market\Export\Entity\Manager::getSource(Market\Export\Entity\Manager::TYPE_IBLOCK_SECTION);

		if ($source instanceof Market\Export\Entity\Reference\HasSectionValues)
		{
			$iblockContext = $context;
			$iblockContext['DISALLOW_SECTION_CHAIN'] = true;

			$result = $source->getSectionListValues($sectionList, $select, $iblockContext);
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	protected function applySectionListOverrides($sectionList, $overrides, $fieldMap)
	{
		foreach ($overrides as $sectionId => $override)
		{
			foreach ($fieldMap as $to => $from)
			{
				if (
					isset($override[$from])
					&& is_scalar($override[$from])
					&& (string)$override[$from] !== ''
				)
				{
					$sectionList[$sectionId][$to] = $override[$from];
				}
			}
		}

		return $sectionList;
	}

	protected function getSectionCrumbsFromIblock($iblockId, $context)
	{
		$listUrl = $this->getIblockListUrl($iblockId, $context['SITE_ID']);
		$result = [];

		if ($listUrl !== null)
		{
			$crumbs = Market\Data\Breadcrumb::getCrumbs($listUrl, $context['SITE_ID']);
			$crumbNumber = $this->getCrumbStartNumber();
			$parentNumber = null;

			foreach ($crumbs as $crumbName)
			{
				++$crumbNumber;

				$result[$crumbNumber] = [
					'ID' => $crumbNumber,
					'NAME' => $crumbName,
					'PARENT_ID' => $parentNumber,
				];

				$parentNumber = $crumbNumber;
			}
		}

		return $result;
	}

	protected function getCrumbStartNumber()
	{
		$maxSectionId = $this->getMaxIblockSectionId();
		$gap = 2000000;

		return $gap * (round($maxSectionId / $gap) + 1);
	}

	protected function getMaxIblockSectionId()
	{
		$result = 0;

		if (Main\Loader::includeModule('iblock'))
		{
			$queryLastsection = \CIBlockSection::GetList(
				[ 'ID' => 'DESC' ],
				[],
				false,
				[ 'ID' ],
				[ 'nTopCount' => 1 ]
			);

			if ($lastSection = $queryLastsection->Fetch())
			{
				$result = (int)$lastSection['ID'];
			}
		}

		return $result;
	}

	protected function getIblockListUrl($iblockId, $siteId)
	{
		$result = null;

		if (Main\Loader::includeModule('iblock'))
		{
			$replaces = Market\Data\Site::getUrlVariables($siteId);
			$iblock = \CIBlock::GetArrayByID($iblockId);

			if ($replaces !== false && isset($iblock['LIST_PAGE_URL']) && trim($iblock['LIST_PAGE_URL']) !== '')
			{
				$template = str_replace($replaces['from'], $replaces['to'], $iblock['LIST_PAGE_URL']);
				$url = \CIBlock::ReplaceDetailUrl($template, [], false);
				$path = (string)parse_url($url, PHP_URL_PATH);

				if ($path !== '')
				{
					$result = $path;
				}
			}
		}

		return $result;
	}

	protected function appendSectionCrumbs($sectionList, $crumbs)
	{
		$lastCrumb = end($crumbs);

		// set parent for root sections

		if ($lastCrumb !== false)
		{
			foreach ($sectionList as &$section)
			{
				if (!isset($section['PARENT_ID']))
				{
					$section['PARENT_ID'] = $lastCrumb['ID'];
				}
			}
			unset($section);
		}

		// prepend crumbs

		$sectionList = $crumbs + $sectionList;

		return $sectionList;
	}

	protected function getUsedSectionIds($context, $iblockLinkIdList = null)
	{
		$result = [];
		$queryFilter = [
			'=SETUP_ID' => $context['SETUP_ID'],
			'=STATUS' => static::STORAGE_STATUS_SUCCESS
		];

		if ($iblockLinkIdList !== null)
		{
			$queryFilter['=IBLOCK_LINK_ID'] = $iblockLinkIdList;
		}

		$query = Market\Export\Run\Storage\OfferTable::getList([
			'group' => [ 'CATEGORY_ID' ],
			'select' => [ 'CATEGORY_ID' ],
			'filter' => $queryFilter
		]);

		while ($row = $query->fetch())
		{
			$categoryId = (int)$row['CATEGORY_ID'];

			if ($categoryId > 0)
			{
				$result[] = $categoryId;
			}
		}

		return $result;
	}

	protected function revertConflictForSectionIdList($sectionIdList, $conflict)
	{
		$result = $sectionIdList;

		foreach ($result as &$sectionId)
		{
			switch ($conflict['TYPE'])
			{
				case 'INCREMENT':
					$sectionId = (int)($sectionId - $conflict['VALUE']);
				break;
			}
		}
		unset($sectionId);

		return $result;
	}

	protected function applyConflictForSectionList($sectionList, $conflict)
	{
		$result = [];

		foreach ($sectionList as $sectionId => $sectionData)
		{
			$newSectionId = $this->applyValueConflict($sectionId, $conflict);
			$sectionData['ID'] = $newSectionId;

			if (isset($sectionData['PARENT_ID']))
			{
				$sectionData['PARENT_ID'] =  $this->applyValueConflict($sectionData['PARENT_ID'], $conflict);
			}

			$result[$newSectionId] = $sectionData;
		}

		return $result;
	}

	protected function buildTagValues($elementId, $dummy, $section, $context)
	{
		$result = new Market\Result\XmlValue();

		$attributes = [
			'id' => $section['ID']
		];

		if (isset($section['PARENT_ID']))
		{
			$attributes['parentId'] = $section['PARENT_ID'];
		}

		$result->addTag('category', $section['NAME'], $attributes);

		return $result;
	}

	protected function getUsedSourceFieldTypes()
	{
		$setup = $this->getSetup();
		$iblockLinkCollection = $setup->getIblockLinkCollection();
		$conflictList = $this->getProcessor()->getConflicts();
		$result = [];

		/** @var \Yandex\Market\Export\IblockLink\Model $iblockLink */
		foreach ($iblockLinkCollection as $iblockLink)
		{
			$iblockId = $iblockLink->getIblockId();
			$tagDescription = $iblockLink->getTagDescription('categoryId');
			$sourceMap = isset($tagDescription['VALUE']) ? $tagDescription['VALUE'] : null;
			$fieldType = null;

			if ($sourceMap === null)
			{
				throw new Main\SystemException(
					Market\Config::getLang('EXPORT_RUN_STEP_CATEGORY_NOT_FOUND_SOURCE_FOR_TAG')
				);
			}

			$iblockContext = $iblockLink->getContext();
			$source = Market\Export\Entity\Manager::getSource($sourceMap['TYPE']);

			if ($source->isVariable())
			{
				throw new Main\SystemException(
					Market\Config::getLang('EXPORT_RUN_STEP_CATEGORY_NO_SUPPORT_FOR_VARIABLE_SOURCE')
				);
			}

			$sourceFields = $source->getFields($iblockContext);

			foreach ($sourceFields as $sourceField)
			{
				if ($sourceField['ID'] === $sourceMap['FIELD'])
				{
					$fieldType = $sourceField['TYPE'];
					break;
				}
			}

			if ($fieldType === null)
			{
				throw new Main\SystemException(
					Market\Config::getLang('EXPORT_RUN_STEP_CATEGORY_NOT_FOUND_SOURCE_FIELD')
				);
			}

			if (!isset($result[$fieldType]))
			{
				$result[$fieldType] = [
					'IBLOCK_LINK_ID' => [],
					'IBLOCK_CONTEXT' => [],
					'IBLOCK_SETTINGS' => [],
					'CONFLICT' => (
						isset($conflictList[$sourceMap['TYPE']][$sourceMap['FIELD']])
							? $conflictList[$sourceMap['TYPE']][$sourceMap['FIELD']]
							: null
					)
				];
			}

			$result[$fieldType]['IBLOCK_LINK_ID'][] = $iblockLink->getId();
			$result[$fieldType]['IBLOCK_CONTEXT'][$iblockId] = $iblockContext;
			$result[$fieldType]['IBLOCK_SETTINGS'][$iblockId] = isset($tagDescription['SETTINGS'])
				? (array)$tagDescription['SETTINGS']
				: [];
		}

		return $result;
	}

	protected function getOfferTagSource(Market\Export\IblockLink\Model $iblockLink, $tagName)
	{
		$result = null;
		$tagDescriptionList = $iblockLink->getTagDescriptionList();

		foreach ($tagDescriptionList as $tagDescription)
		{
			if ($tagDescription['TAG'] === $tagName)
			{
				$result = $tagDescription['VALUE'];
				break;
			}
		}

		return $result;
	}
}