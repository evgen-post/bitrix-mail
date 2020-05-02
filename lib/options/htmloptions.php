<?php
namespace Bx\Mail\Options;

use CAdminTabControl;

/**
 * Class HtmlOptions
 * @package Bx\Mail\Options
 */
class HtmlOptions
{
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    /**
     * @var CAdminTabControl
     */
    protected $tabControl;

    /**
     *
     */
    public function render()
    {
        $this->renderHeaderForm();
        $this->renderTabs();
        $this->renderButtonsForm();
        $this->renderFooterForm();
    }

    /**
     *
     */
    protected function renderTabs()
    {
        foreach ($this->getModuleOptions()->getTabs() as $tab) {
            $this->renderTab($tab);
        }
    }

    /**
     * @param array $field
     * @param array $tab
     * @return string|true
     */
    protected function getRenderField($field=[], $tab=[])
    {
        $div = $tab['DIV'] ?? 'OPTION';
        $field['name'] = $field['name'] ?? sprintf('%s[%s]', $div, $field['code']);
        $field['class'] = $field['class'] ?? '';
        $field['style'] = $field['style'] ?? '';
        $field['attrs'] = $field['attrs'] ?? '';
        $field['value'] = $field['value'] ?? $field['default'] ?? '';
        $field['values'] = $field['values'] ?? [];
        if (is_array($field['values_callback']) || is_callable($field['values_callback'])) {
            $field['values'] = call_user_func($field['values_callback']);
        }
        $this->includeFieldTemplate($field, $tab);
    }

    /**
     * @param $row
     * @param $tab
     */
    protected function includeRowTemplate($row, $tab)
    {
        $div = $tab['DIV'] ?? 'OPTION';
        $row['type'] = $row['type'] ?? 'default';
        $templateFile = sprintf('%s/../../templates/rendersrows/%s.php', __DIR__, $row['type']);
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            include sprintf('%s/../../templates/rendersrows/default.php', __DIR__);
        }
    }

    /**
     * @param $row
     * @param $div
     */
    protected function includeFieldTemplate($row, $div)
    {
        $row['type'] = $row['type'] ?? 'default';
        $templateFile = sprintf('%s/../../templates/rendersfields/%s.php', __DIR__, $row['type']);
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            include sprintf('%s/../../templates/rendersfields/default.php', __DIR__);
        }
    }

    /**
     * @param array $row
     * @param array $tab
     * @return null
     */
    protected function renderRow($row=[], $tab=[])
    {
        $row['is_show'] = $row['is_show'] ?? true;
        if (is_array($row['is_show']) || is_callable($row['is_show'])) {
            $row['is_show'] = (bool)call_user_func($row['is_show']);
        }
        if (!$row['is_show']) {
            return null;
        }
        $this->includeRowTemplate($row, $tab);
    }

    /**
     * @param array $tab
     */
    protected function renderTab($tab=[])
    {
        $this->getTabControl()->BeginNextTab();
        if (!empty($tab['rows'])) {
            foreach ($tab['rows'] as $row) {
                $this->renderRow($row, $tab);
            }
        }
    }
    /**
     *
     */
    protected function renderFooterForm()
    {
        $this->getTabControl()->End();
        echo <<<HTML
</form>
HTML;
    }

    /**
     *
     */
    protected function renderHeaderForm()
    {
        global $APPLICATION;
        $mid = htmlspecialcharsbx(ModuleOptions::MODULE_ID);
        $bxSessIdPost = bitrix_sessid_post();
        $lang = LANG;
        echo <<<HTML
<form enctype="multipart/form-data" name="main_options" method="POST" action="{$APPLICATION->GetCurPage()}?mid={$mid}&amp;lang={$lang}">
    {$bxSessIdPost}
HTML;
        $this->getTabControl()->Begin();

    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

    /**
     * @return CAdminTabControl
     */
    public function getTabControl(): CAdminTabControl
    {
        return $this->tabControl;
    }

    /**
     *
     */
    protected function renderButtonsForm()
    {
        global $APPLICATION, $USER;
        $languageId = LANGUAGE_ID;
        $midEnc = urlencode(ModuleOptions::MODULE_ID);
        $bxSessIdGet = bitrix_sessid_get();
        $this->getTabControl()->Buttons();
        $printDisabled = (is_object($USER) && !$USER->CanDoOperation('edit_other_settings')) ? ' disabled' : '';
        $applyTitle = GetMessage("MAIN_OPT_APPLY_TITLE");
        $class = ($_REQUEST["back_url_settings"] == "") ? ' class="adm-btn-save"' : '';
        $backUrlSettings = htmlspecialcharsbx($_REQUEST["back_url_settings"]);
        $optCancel = '';
        if($_REQUEST["back_url_settings"] <> "") {
            $valueCancel = GetMessage("MAIN_OPT_CANCEL");
            $titleCancel = GetMessage("MAIN_OPT_CANCEL_TITLE");
            $windowLocation = htmlspecialcharsbx(\CUtil::JSEscape($_REQUEST["back_url_settings"]));
            $optCancel = <<<HTML
<input type="button" name="" value="{$valueCancel}" title="{$titleCancel}" onclick="window.location='{$windowLocation}'">
HTML;
        }
        echo <<<HTML
    <script type="text/javascript">
        function RestoreDefaults()
        {
            if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "{$APPLICATION->GetCurPage()}?RestoreDefaults=Y&lang={$languageId}&mid={$midEnc}&{$bxSessIdGet}";
        }
        BX.ready(function(){
            var f = document.forms['main_options'];
            if(f.use_time_zones)
                f.default_time_zone.disabled = f.auto_time_zone.disabled = !f.use_time_zones.checked;
        });
    </script>
    <input{$printDisabled} type="submit" name="Apply" value="Сохранить" title="{$applyTitle}"{$class}>
    {$optCancel}
    <input type="hidden" name="Update" value="Y">
    <input type="hidden" name="back_url_settings" value="{$backUrlSettings}">
HTML;

    }

    /**
     * @param ModuleOptions $moduleOptions
     * @return HtmlOptions
     */
    public function setModuleOptions(ModuleOptions $moduleOptions): HtmlOptions
    {
        $this->moduleOptions = $moduleOptions;
        $this->tabControl = new CAdminTabControl('tabControl', $this->moduleOptions->getTabs());
        return $this;
    }

}