<?php

namespace Yandex\Market\Ui\UserField;

use Yandex\Market;
use Bitrix\Main;

class StringType extends \CUserTypeString
{
	use Market\Reference\Concerns\HasLang;

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	function getUserTypeDescription()
	{
		$result = parent::getUserTypeDescription();

		if (!empty($result['USE_FIELD_COMPONENT']))
		{
			$result['USE_FIELD_COMPONENT'] = false;
		}

		return $result;
	}

	function GetEditFormHtmlMulty($userField, $htmlControl)
	{
		$values = Helper\Value::asMultiple($userField, $htmlControl);
		$values = static::sanitizeMultipleValues($values);
		$valueIndex = 0;

		if (empty($values)) { $values[] = ''; }

		$result = sprintf('<table id="table_%s">', $userField['FIELD_NAME']);

		foreach ($values as $value)
		{
			$result .= '<tr><td>';
			$result .= static::GetEditFormHTML($userField, [
				'NAME' => $userField['FIELD_NAME'] . '[' . $valueIndex . ']',
				'VALUE' => $value,
			]);
			$result .= '</td></tr>';

			++$valueIndex;
		}

		$result .= '<tr><td style="padding-top: 6px;">';
		$result .= static::getMultipleAddButton($userField);
		$result .= '</td></tr>';
		$result .= '</table>';
		$result .= static::getMultipleAutoSaveScript($userField);

		return $result;
	}

	protected static function getMultipleAddButton($userField)
	{
		Market\Ui\Assets::loadPlugin('Ui.StringType');

		$fieldNameSanitized = str_replace('_', 'x', $userField["FIELD_NAME"]);

		return sprintf(
			'<input type="button" value="%1$s" onClick="ymAddNewRow(\'table_%2$s\', \'%3$s|%2$s|%2$s_old_id\')"></td></tr>',
			Main\Localization\Loc::getMessage('USER_TYPE_PROP_ADD'),
			$userField["FIELD_NAME"],
			$fieldNameSanitized
		);
	}

	protected static function getMultipleAutoSaveScript($userField)
	{
		$fieldNameSanitized = str_replace('_', 'x', $userField["FIELD_NAME"]);

		return sprintf('<script type="text/javascript">'
			. 'BX.addCustomEvent("onAutoSaveRestore", function(ob, data) {'
				. 'for (var i in data){'
					. 'if (i.substring(0, %2$s)=="%3$s[") {'
						. 'addNewRow("table_%1$s", "%4$s|%1$s|%1$s_old_id")'
					. '}}})'
			. '</script>',
			$userField['FIELD_NAME'],
			strlen($userField['FIELD_NAME']) + 1,
			\CUtil::JSEscape($userField['FIELD_NAME']),
			$fieldNameSanitized
		);
	}

	function GetEditFormHTML($userField, $htmlControl)
	{
		$attributes = Helper\Attributes::extractFromSettings($userField['SETTINGS']);

		$result = static::getEditInput($userField, $htmlControl);
		$result = Helper\Attributes::insertInput($result, $attributes);

		if (isset($userField['SETTINGS']['COPY_BUTTON']))
		{
			$result .= ' ' . static::getCopyButton($userField, $htmlControl);
		}

		if (isset($userField['SETTINGS']['ROWS']) && $userField['SETTINGS']['ROWS'] > 1)
		{
			$htmlControl['VALIGN'] = 'top';
		}

		return $result;
	}

	function GetAdminListViewHtml($userField, $htmlControl)
	{
		$value = (string)Helper\Value::asSingle($userField, $htmlControl);

		return $value !== '' ? $value : '&nbsp;';
	}

	protected static function sanitizeMultipleValues(array $values)
	{
		$result = [];

		foreach ($values as $value)
		{
			if (is_scalar($value) && (string)$value !== '')
			{
				$result[] = htmlspecialcharsbx($value);
			}
		}

		return $result;
	}

	protected static function getEditInput($userField, $htmlControl)
	{
		if ($userField['ENTITY_VALUE_ID'] < 1 && (string)$userField['SETTINGS']['DEFAULT_VALUE'] !== '')
		{
			$htmlControl['VALUE'] = htmlspecialcharsbx($userField['SETTINGS']['DEFAULT_VALUE']);
		}

		if ($userField['SETTINGS']['ROWS'] < 2)
		{
			$htmlControl['VALIGN'] = 'middle';
			
			return '<input type="text" '.
				'name="'.$htmlControl['NAME'].'" '.
				'size="'.$userField['SETTINGS']['SIZE'].'" '.
				($userField['SETTINGS']['MAX_LENGTH']>0? 'maxlength="'.$userField['SETTINGS']['MAX_LENGTH'].'" ': '').
				'value="'.$htmlControl['VALUE'].'" '.
				($userField['EDIT_IN_LIST'] !== 'Y' ? 'disabled="disabled" ': '').
				'>';
		}
		else
		{
			return '<textarea '.
				'name="'.$htmlControl['NAME'].'" '.
				'cols="'.$userField['SETTINGS']['SIZE'].'" '.
				'rows="'.$userField['SETTINGS']['ROWS'].'" '.
				($userField['SETTINGS']['MAX_LENGTH']>0? 'maxlength="'.$userField['SETTINGS']['MAX_LENGTH'].'" ': '').
				($userField['EDIT_IN_LIST'] !== 'Y' ? 'disabled="disabled" ': '').
				'>'.$htmlControl['VALUE'].'</textarea>';
		}
	}

	protected static function getCopyButton($userField, $htmlControl)
	{
		static::loadMessages();

		Market\Ui\Assets::loadPlugin('Ui.Input.CopyClipboard');
		Market\Ui\Assets::loadMessages([
			'INPUT_COPY_CLIPBOARD_SUCCESS',
			'INPUT_COPY_CLIPBOARD_FAIL',
		]);

		return
			'<button class="adm-btn js-plugin-click" type="button" data-plugin="Ui.Input.CopyClipboard">'
				. static::getLang('UI_USER_FIELD_STRING_COPY')
			. '</button>';
	}
}