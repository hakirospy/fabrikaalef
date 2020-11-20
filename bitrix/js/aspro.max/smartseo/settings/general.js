'use strict';

BX.ready(function ()
{
  new AsproUI.Form('general_setting_form');
  
  (function ()
  {
    let elGeneralSettingForm = document.getElementById('general_setting_form'),
      elFilterRuleNameTemplate = elGeneralSettingForm.querySelector('[page-role="control-engine-filter-rule-name"]');

    if (elFilterRuleNameTemplate && phpObjectGeneralSetting.urls.MENU_FILTER_NAME) {
      new AsproUI.Form.ControlTemplateEngine(elFilterRuleNameTemplate, false, {
        urlMenuResponse: phpObjectGeneralSetting.urls.MENU_FILTER_NAME,
      });
    }

  }());  
  
})