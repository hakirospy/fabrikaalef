<?php

/**
 *  @var string $alias
 *  @var array  $data
 *  @var array $listSites
 */
use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

\Bitrix\Main\UI\Extension::load('ui.notification');

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/sites.js');

$pageTitle = Loc::getMessage('SMARTSEO_PAGE_TITLE');

$APPLICATION->setTitle($pageTitle);

$_tabControlItems = [];

foreach ($listSites as $site) {
    $_tabControlItems[] = [
        'DIV' => 'edit_' . $site['LID'],
        'TAB' => $site['NAME'],
        'ICON' => '',
        'TITLE' => $site['NAME'],
    ];
}

$adminTabControl = new CAdminTabControl('setting_tab_control', $_tabControlItems, false);
?>
<div class="aspro-smartseo__multi-form-wrapper">

  <? $adminTabControl->Begin() ?>

  <? foreach ($listSites as $site) : ?>
    <?
        $isFriendlyUrl = false;
        if($data[$site['LID']]['SMARTFILTER_FRIENDLY']) {
            $isFriendlyUrl = $data[$site['LID']]['SMARTFILTER_FRIENDLY'] == 'Y' ? true : false;
        } elseif($data['default']['SMARTFILTER_FRIENDLY']) {
            $isFriendlyUrl = $data['default']['SMARTFILTER_FRIENDLY'] == 'Y' ? true : false;
        }
    ?>

    <? $adminTabControl->BeginNextTab() ?>
    <tr>
      <td width="100%" colspan="2">
        <div class="aspro-smartseo__multi-form-detail">
          <form id="site_setting_form_<?= $site['LID'] ?>" method="POST" action="<?= Helper::url('setting/update_site_settings') ?>" enctype="multipart/form-data">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="<?= $alias ?>[SITE_ID]" value="<?= $site['LID'] ?>">

            <div form-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert" style="display: none;">
              <span class="ui-alert-message" form-role="alert-body"></span>
            </div>

            <div class="aspro-smartseo__form-detail__body">
              <div class="adm-detail-content-item-block">
                <table class="adm-detail-content-table edit-table">
                  <tbody>
                    <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_URL_PAGE_SETTINGS') ?></td></tr>

                    <tr>
                      <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_SEF_FOLDER') ?>: </td>
                      <td width="60%" class="adm-detail-content-cell-r">
                        <div class="aspro-smartseo__form-control">
                          <div page-role="control-engine-url" data-category="url_sef_folder">
                            <div class="aspro-smartseo__form-control__input-menu-wrapper">
                              <div control-role="input-wrapper">
                                <input control-role="input" class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[URL_SEF_FOLDER]"
                                       value="<?= $data[$site['LID']]['URL_SEF_FOLDER']
                                          ? htmlspecialchars($data[$site['LID']]['URL_SEF_FOLDER'])
                                          : $data['default']['URL_SEF_FOLDER'] ?>">
                              </div>
                              <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr>
                        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SMARTFILTER_FRIENDLY') ?>: </td>
                        <td width="60%" class="adm-detail-content-cell-r">
                          <div class="aspro-smartseo__form-control">
                            <? $_list = [
                              'Y' => Loc::getMessage('SMARTSEO_FORM_VALUE_Y'),
                              'N' => Loc::getMessage('SMARTSEO_FORM_VALUE_N'),
                            ]
                            ?>
                            <select page-role="form-field-SMARTFILTER_FRIENDLY" class="aspro-smartseo__form-control__select" name="<?= $alias ?>[SMARTFILTER_FRIENDLY]">
                              <? foreach ($_list as $value => $label) : ?>
                              <?
                                $_selected = false;
                                if($data[$site['LID']]['SMARTFILTER_FRIENDLY']) {
                                    $_selected = $data[$site['LID']]['SMARTFILTER_FRIENDLY'];
                                } elseif($data['default']['SMARTFILTER_FRIENDLY']) {
                                    $_selected = $data['default']['SMARTFILTER_FRIENDLY'];
                                }
                              ?>
                              <option value="<?= $value ?>" <?= $value == $_selected ? 'selected' : '' ?>><?= $label ?></option>
                              <? endforeach ?>
                            </select>
                          </div>
                        </td>
                    </tr>

                    <tr page-role="smartfilter-url-wrapper" style="<?= $isFriendlyUrl ? 'display: table-row' : 'display: none' ?>">
                      <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_TEMPLATE_SMARTFILTER') ?>: </td>
                      <td width="60%" class="adm-detail-content-cell-r">
                        <div class="aspro-smartseo__form-control">
                          <div page-role="control-engine-url" data-category="url_template_smartfilter">
                            <div class="aspro-smartseo__form-control__input-menu-wrapper">
                              <div control-role="input-wrapper">
                                <input control-role="input" class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[URL_TEMPLATE_SMARTFILTER]"
                                       value="<?= $data[$site['LID']]['URL_TEMPLATE_SMARTFILTER']
                                          ? htmlspecialchars($data[$site['LID']]['URL_TEMPLATE_SMARTFILTER'])
                                          : $data['default']['URL_TEMPLATE_SMARTFILTER'] ?>">
                              </div>
                              <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr page-role="not-friendly-controls-wrapper" style="<?= !$isFriendlyUrl ? 'display: table-row' : 'display: none' ?>">
                      <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_SECTION') ?>: </td>
                      <td width="60%" class="adm-detail-content-cell-r">
                        <div class="aspro-smartseo__form-control">
                          <div page-role="control-engine-url" data-category="url_section">
                            <div class="aspro-smartseo__form-control__input-menu-wrapper">
                              <div control-role="input-wrapper">
                                <input control-role="input" class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[URL_SECTION]"
                                       value="<?= $data[$site['LID']]['URL_SECTION']
                                          ? htmlspecialchars($data[$site['LID']]['URL_SECTION'])
                                          : $data['default']['URL_SECTION'] ?>">
                              </div>
                              <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr page-role="not-friendly-controls-wrapper" style="<?= !$isFriendlyUrl ? 'display: table-row' : 'display: none' ?>">
                      <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SMARTFILTER_FILTER_NAME') ?>: </td>
                      <td width="60%" class="adm-detail-content-cell-r">
                        <div class="aspro-smartseo__form-control">
                          <input control-role="input" class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[SMARTFILTER_FILTER_NAME]"
                                value="<?= $data[$site['LID']]['SMARTFILTER_FILTER_NAME']
                                ? htmlspecialchars($data[$site['LID']]['SMARTFILTER_FILTER_NAME'])
                                : $data['default']['SMARTFILTER_FILTER_NAME'] ?>">
                        </div>
                      </td>
                    </tr>

                    <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_NEW_URL_PAGE_SETTINGS') ?></td></tr>

                    <tr>
                      <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NEW_URL_SECTION') ?>: </td>
                      <td width="60%" class="adm-detail-content-cell-r">
                        <div class="aspro-smartseo__form-control">
                          <div page-role="control-engine-url" data-category="new_url_section">
                            <div class="aspro-smartseo__form-control__input-menu-wrapper">
                              <div control-role="input-wrapper">
                                <input control-role="input" class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[NEW_URL_SECTION]"
                                       value="<?= $data[$site['LID']]['NEW_URL_SECTION']
                                          ? htmlspecialchars($data[$site['LID']]['NEW_URL_SECTION'])
                                          : $data['default']['NEW_URL_SECTION'] ?>">
                              </div>
                              <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                  </tbody>
                </table>
              </div>

              <div class="aspro-smartseo__form-detail__wrapper-footer">
                <div class="aspro-smartseo__form-detail__footer__toolbar">
                  <div class="aspro-smartseo__form-detail__buttons">
                    <button form-role="apply" data-action="apply" class="ui-btn ui-btn-sm ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
                      <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </form>
        </div>
      </td>
    </tr>

    <script>
        new SiteSettingForm('site_setting_form_<?= $site['LID'] ?>', <?=
              CUtil::PhpToJSObject([
                'data' => [
                  'SITE_NAME' => $site['NAME'],
                ],
                'urls' => [
                    'MENU_URL_TEMPLATE' => Helper::url('setting/get_menu_url_template'),
                ],
            ])
            ?>);

        BX.message({
            SMARTSEO_MESSAGE_SAVE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_SAVE_SUCCESS') ?>',
        });
    </script>

  <? endforeach ?>

  <? $adminTabControl->End() ?>

</div>