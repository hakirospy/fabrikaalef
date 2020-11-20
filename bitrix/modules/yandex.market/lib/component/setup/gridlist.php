<?php

namespace Yandex\Market\Component\Setup;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class GridList extends Market\Component\Model\GridList
	implements Market\Component\Concerns\UiServiceInterface
{
	use Market\Component\Concerns\HasGroup;
	use Market\Component\Concerns\HasUiService;

	protected $groupId;

	public function prepareComponentParams($params)
	{
		global $APPLICATION;

		$result = parent::prepareComponentParams($params);
		$result['SERVICE'] = trim($params['SERVICE']);

		if ($result['SERVICE'] !== '')
		{
			$result['BASE_URL'] = $APPLICATION->GetCurPageParam(
				http_build_query([ 'service' => $result['SERVICE'] ]),
				[ 'service' ]
			);
		}

		return $result;
	}

	protected function getReferenceFields()
	{
		$result = parent::getReferenceFields();
		$result['IBLOCK'] = [];

		return $result;
	}

	public function getDefaultFilter()
	{
		$result = parent::getDefaultFilter();
		$serviceFilter = $this->getUiServiceFilter();

		if ($serviceFilter !== null)
		{
			$result[] = $serviceFilter;
		}

		return $result;
	}

	public function processAjaxAction($action, $data)
	{
		$result = null;

		switch ($action)
		{
			case 'move_group':
				$this->processMoveGroupForGroups($data);
				$this->processMoveGroupForItems($data);
			break;

			case 'add_group':
				$this->processMoveGroupForGroups($data);
				$this->processAddGroupForItems($data);
			break;

			default:
				parent::processAjaxAction($action, $data);
			break;
		}

		return $result;
	}

	protected function processMoveGroupForGroups($data)
	{
		$targetGroup = $this->getAjaxActionTargetGroup();
		$selectedIds = $this->getActionSelectedGroups($data);

		foreach ($selectedIds as $selectedId)
		{
			$this->moveGroup($selectedId, $targetGroup);
		}
	}

	protected function processMoveGroupForItems($data)
	{
		$targetGroup = $this->getAjaxActionTargetGroup();

		// delete exists links

		$setupFilter = $this->getActionSelectedFilter($data);
		$filter = $this->mixFilterEntity($setupFilter, 'SETUP');

		$this->deleteGroupLinks($filter);

		// add new links

		if ($targetGroup > 0)
		{
			$selectedIds = $this->getActionSelectedIds($data);
			$this->addGroupLinks($selectedIds, $targetGroup);
		}
	}

	protected function processAddGroupForItems($data)
	{
		$targetGroup = $this->getAjaxActionTargetGroup();
		$selectedIds = $this->getActionSelectedIds($data);
		$existsLinks = $this->getExistsGroupLinks($targetGroup);
		$withoutLinks = $this->getSetupWithoutGroupLinks($selectedIds);
		$newIds = array_diff($selectedIds, $existsLinks);

		if ($targetGroup !== 0)
		{
			$this->addGroupLinks($withoutLinks, 0);
		}

		$this->addGroupLinks($newIds, $targetGroup);
	}

	protected function processDeleteAction($data)
	{
		parent::processDeleteAction($data);
		$this->processDeleteGroups($data);
	}

	protected function processDeleteGroups($data)
	{
		$selectedGroups = $this->getActionSelectedGroups($data);
		$childrenGroups = $this->getGroupChildren($selectedGroups, true);
		$allGroups = array_merge($selectedGroups, $childrenGroups);
		$items = $this->getGroupItems($allGroups);
		$siblingItems = $this->getSiblingGroupItems($allGroups, $items);
		$itemsWithoutSiblings = array_diff($items, $siblingItems);

		foreach ($itemsWithoutSiblings as $itemId)
		{
			$this->deleteItem($itemId);
		}

		foreach ($allGroups as $groupId)
		{
			$this->deleteGroup($groupId);
		}
	}

	protected function mixFilterEntity($filter, $entityName)
	{
		$filterBuilder = new \CSQLWhere();
		$result = [];

		foreach ($filter as $key => $value)
		{
			if (!is_numeric($key))
			{
				$operation = $filterBuilder->MakeOperation($key);
				$conditionLength = strlen($key) - strlen($operation['FIELD']);
				$condition = substr($key, 0, $conditionLength);
				$newKey = $condition . $entityName . '.' . $operation['FIELD'];

				$result[$newKey] = $value;
			}
			else if (is_array($value))
			{
				$result[$key] = $this->mixFilterEntity($value, $entityName);
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	protected function deleteGroupLinks($filter)
	{
		$dataClass = $this->getGroupLinkDataClass();

		$dataClass::deleteBatch([
			'filter' => $filter,
		]);
	}

	protected function getExistsGroupLinks($groupId)
	{
		$result = [];
		$dataClass = $this->getGroupLinkDataClass();

		$query = $dataClass::getList([
			'filter' => [ '=GROUP_ID' => $groupId ],
			'select' => [ 'SETUP_ID' ],
		]);

		while ($row = $query->fetch())
		{
			$result[] = (int)$row['SETUP_ID'];
		}

		return $result;
	}

	protected function getSetupWithoutGroupLinks($setupIds)
	{
		$result = [];

		$query = Market\Export\Setup\Table::getList([
			'filter' => [ '=ID' => $setupIds, 'GROUP_LINK.GROUP_ID' => false ],
			'select' => [ 'ID', 'GROUP_ID' => 'GROUP_LINK.GROUP_ID' ],
		]);

		while ($row = $query->fetch())
		{
			if ((string)$row['GROUP_ID'] !== '') { continue; } // ignore '0'

			$result[] = (int)$row['ID'];
		}

		return $result;
	}

	protected function getGroupItems($groupIds)
	{
		$groupLinkDataClass = $this->getGroupLinkDataClass();
		$result = [];

		if (empty($groupIds)) { return $result; }

		$query = $groupLinkDataClass::getList([
			'filter' => [ '=GROUP_ID' => $groupIds ],
			'select' => [ 'SETUP_ID' ]
		]);

		while ($row = $query->fetch())
		{
			$result[] = (int)$row['SETUP_ID'];
		}

		return array_unique($result);
	}

	protected function getSiblingGroupItems($groupIds, $itemIds)
	{
		$groupLinkDataClass = $this->getGroupLinkDataClass();
		$result = [];

		if (empty($groupIds) || empty($itemIds)) { return $result; }

		$query = $groupLinkDataClass::getList([
			'filter' => [ '!=GROUP_ID' => $groupIds, '=SETUP_ID' => $itemIds ],
			'select' => [ 'SETUP_ID' ]
		]);

		while ($row = $query->fetch())
		{
			$result[] = (int)$row['SETUP_ID'];
		}

		return array_unique($result);
	}

	protected function getGroupChildren($groupIds, $recursive = false, array $foundGroups = [])
	{
		$dataClass = $this->getGroupDataClass();
		$result = [];

		if (empty($groupIds)) { return $result; }

		$query = $dataClass::getList([
			'filter' => [ '=PARENT_ID' => $groupIds ],
			'select' => [ 'ID' ],
		]);

		while ($row = $query->fetch())
		{
			$result[] = (int)$row['ID'];
		}

		if ($recursive && !empty($result))
		{
			$foundGroups = array_merge($foundGroups, $groupIds);
			$notCheckedGroups = array_diff($result, $foundGroups);

			if (!empty($notCheckedGroups))
			{
				$recursiveChildren = $this->getGroupChildren($result, true, $foundGroups);
				$result = array_merge($result, $recursiveChildren);
			}
		}

		return $result;
	}

	protected function moveGroup($groupId, $parentId)
	{
		$dataClass = $this->getGroupDataClass();
		$updateResult = $dataClass::update($groupId, [ 'PARENT_ID' => $parentId ]);

		Market\Result\Facade::handleException($updateResult);
	}

	protected function deleteGroup($groupId)
	{
		$dataClass = $this->getGroupDataClass();
		$deleteResult = $dataClass::delete($groupId);

		Market\Result\Facade::handleException($deleteResult);
	}

	protected function addGroupLinks($setupIds, $groupId)
	{
		$dataClass = $this->getGroupLinkDataClass();

		foreach ($setupIds as $setupId)
		{
			$dataClass::add([
				'SETUP_ID' => $setupId,
				'GROUP_ID' => $groupId,
			]);
		}
	}

	protected function getAjaxActionTargetGroup()
	{
		$request = Main\Context::getCurrent()->getRequest();

		return (int)$request->get('group_to_move');
	}

	protected function filterGroupPrimaries($ids, $revert = false)
	{
		$result = [];

		foreach ($ids as $id)
		{
			$isGroup = (strpos($id, 'G') === 0);

			if ($isGroup === $revert)
			{
				if ($isGroup) { $id = substr($id, 1); }

				$result[] = (int)$id;
			}
		}

		return $result;
	}

	protected function getActionSelectedIds($data)
	{
		$result = parent::getActionSelectedIds($data);

		return $this->filterGroupPrimaries($result);
	}

	protected function getActionSelectedGroups($data)
	{
		$result = parent::getActionSelectedIds($data);

		return $this->filterGroupPrimaries($result, true);
	}

	public function getFields(array $select = [])
	{
		$result = parent::getFields($select);
		$result = $this->excludeServiceDisabledFields($result);
		$result = $this->allowGroupFields($result);

		if (isset($result['GROUP']))
		{
			$result['GROUP'] = $this->modifyGroupField($result['GROUP']);
		}

		if (isset($result['EXPORT_SERVICE']))
		{
			$result['EXPORT_SERVICE'] = $this->modifyExportServiceField($result['EXPORT_SERVICE']);

			$this->resolveExportServiceFilter($result['EXPORT_SERVICE']);
		}

		if (isset($result['EXPORT_FORMAT'], $result['EXPORT_SERVICE']))
		{
			$result['EXPORT_FORMAT'] = $this->modifyExportFormatField($result['EXPORT_FORMAT'], $result['EXPORT_SERVICE']);
		}

		return $result;
	}

	protected function excludeServiceDisabledFields($fields)
	{
		$uiService = $this->getUiService();
		$disabledFields = $uiService->getExportSetupDisabledFields();
		$disabledFieldsMap = array_flip($disabledFields);

		return array_diff_key($fields, $disabledFieldsMap);
	}

	protected function modifyGroupField($field)
	{
		if (!isset($field['SETTINGS'])) { $field['SETTINGS'] = []; }

		$field['USER_TYPE'] = Market\Ui\UserField\Manager::getUserType('enumeration');
		$field['SETTINGS']['ALLOW_NO_VALUE'] = 'N';
		$field['VALUES'] = $this->getGroupTreeEnum();
		$field['SELECTABLE'] = false;

		return $field;
	}

	protected function modifyExportServiceField($field)
	{
		if (isset($field['VALUES']))
		{
			$uiService = $this->getUiService();
			$exportServices = $uiService->getExportServices();
			$exportServicesMap = array_flip($exportServices);
			$isInverted = $uiService->isInverted();

			foreach ($field['VALUES'] as $optionKey => $option)
			{
				$isExists = isset($exportServicesMap[$option['ID']]);

				if ($isExists === $isInverted)
				{
					unset($field['VALUES'][$optionKey]);
				}
			}
		}

		return $field;
	}

	protected function resolveExportServiceFilter($field)
	{
		if (!isset($field['VALUES']) || count($field['VALUES']) < 2)
		{
			$filterFields = $this->getComponentParam('FILTER_FIELDS');
			$filterIndex = array_search($field['FIELD_NAME'], $filterFields, true);

			if ($filterIndex !== false)
			{
				array_splice($filterFields, $filterIndex, 1);

				$this->setComponentParam('FILTER_FIELDS', $filterFields);
			}
		}
	}

	protected function modifyExportFormatField($field, $serviceField)
	{
		if (isset($field['VALUES'], $serviceField['VALUES']))
		{
			$exportServices = array_column($serviceField['VALUES'], 'ID');
			$existsTypes = [];

			foreach ($exportServices as $service)
			{
				$types = Market\Export\Xml\Format\Manager::getTypeList($service);

				if ($types !== null)
				{
					$existsTypes += array_flip($types);
				}
			}

			foreach ($field['VALUES'] as $optionKey => $option)
			{
				if (!isset($existsTypes[$option['ID']]))
				{
					unset($field['VALUES'][$optionKey]);
				}
			}
		}

		return $field;
	}

	/**
	 * @return Main\Entity\DataManager
	 */
	protected function getGroupDataClass()
	{
		return Market\Export\Setup\Internals\GroupTable::class;
	}

	/**
	 * @return Main\Entity\DataManager
	 */
	protected function getGroupLinkDataClass()
	{
		return Market\Export\Setup\Internals\GroupLinkTable::class;
	}

	public function load(array $queryParameters = [])
	{
		$groupId = $this->findLoadParametersGroup($queryParameters);
		$groupParameters = $this->makeLoadGroupParameters($groupId);

		$this->setGroupId($groupId);

		return array_merge(
			$this->loadGroups($groupParameters),
			parent::load($queryParameters)
		);
	}

	protected function findLoadParametersGroup(array $queryParameters)
	{
		$result = null;
		$variants = [
			'GROUP',
			'GROUP.ID',
		];

		foreach ($variants as $variant)
		{
			if (isset($queryParameters['filter'][$variant]))
			{
				$result = $queryParameters['filter'][$variant];
				break;
			}
		}

		return $result;
	}

	protected function makeLoadGroupParameters($groupId)
	{
		$result = [];

		if ($groupId !== null)
		{
			$result['filter'] = [ '=PARENT_ID' => $groupId ];
		}

		return $result;
	}

	protected function loadGroups(array $queryParameters = [])
	{
		$dataClass = $this->getGroupDataClass();
		$result = [];

		if (!isset($queryParameters['filter'])) { $queryParameters['filter'] = []; }

		$queryParameters['filter'][] = $this->getUiServiceFilter('UI_SERVICE');

		$query = $dataClass::getList($queryParameters);

		while ($group = $query->fetch())
		{
			$result[] = [
				'PRIMARY' => $group['ID'],
				'ID' => 'G' . $group['ID'],
				'NAME' => $group['NAME'],
				'ROW_TYPE' => 'GROUP',
				'ROW_ICON' => 'iblock-section-icon',
			];
		}

		return $result;
	}

	public function getContextMenu()
	{
		return array_filter([
			$this->getContextMenuAdd(),
			$this->getContextMenuGroupEdit(),
			$this->getContextMenuGroupUp(),
		]);
	}

	protected function getContextMenuAdd()
	{
		$addUrl = (string)$this->getComponentParam('ADD_URL');
		$groupId = $this->getGroupId();

		if ($addUrl === '') { return null; }

		if ($groupId > 0)
		{
			$addUrl .= (strpos($addUrl, '?') === false ? '?' : '&') . 'parent=' . (int)$groupId;
		}

		return [
			'TEXT' => Market\Config::getLang('COMPONENT_SETUP_GRID_LIST_CONTEXT_ADD'),
			'LINK' => $addUrl,
			'ICON' => 'btn_new'
		];
	}

	protected function getContextMenuGroupEdit()
	{
		$groupEditUrl = (string)$this->getComponentParam('GROUP_EDIT_URL');
		$groupId = $this->getGroupId();

		if ($groupEditUrl === '') { return null; }

		if ($groupId > 0)
		{
			$groupEditUrl .=
				(strpos($groupEditUrl, '?') === false ? '?' : '&')
				. 'parent=' . (int)$groupId;
		}

		return [
			'TEXT' => Market\Config::getLang('COMPONENT_SETUP_GRID_LIST_CONTEXT_GROUP_ADD'),
			'LINK' => $groupEditUrl,
		];
	}

	protected function getContextMenuGroupUp()
	{
		global $APPLICATION;

		$groupId = $this->getGroupId();
		$parentId = $groupId > 0 ? $this->getGroupParentId($groupId) : null;

		if ($parentId === null) { return null; }

		return [
			'TEXT' => Market\Config::getLang('COMPONENT_SETUP_GRID_LIST_CONTEXT_GROUP_UP'),
			'LINK' => $APPLICATION->GetCurPageParam('find_group=' . $parentId . '&set_filter=Y', [
				'find_group',
				'table_id',
				'mode',
			]),
		];
	}

	public function getGroupActions()
	{
		return [
			'move_group' => Market\Config::getLang('COMPONENT_SETUP_GRID_LIST_ACTION_GROUP_MOVE'),
			'add_group' => Market\Config::getLang('COMPONENT_SETUP_GRID_LIST_ACTION_GROUP_ADD'),
			'group_chooser' => $this->getGroupActionGroupChooser(),
		];
	}

	protected function getGroupActionGroupChooser()
	{
		$fields = $this->getComponentResult('FIELDS');
		$variants = isset($fields['GROUP']['VALUES']) ? (array)$fields['GROUP']['VALUES'] : [];

		$groups = '<div id="group_to_move" style="display: none;">';
		$groups .= '<select name="group_to_move">';

		foreach ($variants as $variant)
		{
			$groups .= sprintf(
				'<option value="%s">%s</option>',
				$variant['ID'],
				$variant['VALUE']
			);
		}

		$groups .= '</select>';
		$groups .= '</div>';

		return [
			'type' => 'html',
			'value' => $groups,
		];
	}

	public function getGroupActionParams()
	{
		return [
			'select_onchange' => "BX('group_to_move').style.display = (this.value == 'move_group' || this.value == 'add_group'? 'block':'none');",
		];
	}

	protected function setGroupId($groupId)
	{
		$this->groupId = $groupId;
	}

	protected function getGroupId()
	{
		return $this->groupId;
	}

	protected function getGroupParentId($id)
	{
		$result = null;
		$dataClass = $this->getGroupDataClass();

		$query = $dataClass::getList([
			'filter' => [ '=ID' => $id ],
			'select' => [ 'PARENT_ID' ],
		]);

		if ($row = $query->fetch())
		{
			$result = (int)$row['PARENT_ID'];
		}

		return $result;
	}
}