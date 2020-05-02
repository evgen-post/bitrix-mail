<?php
namespace Bx\Mail\Options;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bx\Mail\MailOption;

/**
 * Class ModuleOptions
 * @package Bx\Mail\Options
 */
class ModuleOptions
{
    const MODULE_ID = 'bx.mail';
    /**
     * @var HtmlOptions
     */
    protected $htmlOptions;
    protected $loadedOptions = [];
    protected $tabs = [];
    /**
     * @var ModuleOptionsActions
     */
    protected $moduleOptionsActions;
    /**
     *
     */
    protected function loadTabs()
    {
        $this->tabs = [
            [
                "DIV" => MailOption::OPTION_GROUP_MAIL,
                "TAB" => "Настройки и ограничения",
                "ICON" => "main_settings",
                "TITLE" => "Настройки и ограничения почтовых отправлений",
                'rows' => [
                    [
                        'type' => 'header',
                        'label' => 'Настройка отправки почты',
                    ],
                    [
                        'label' => 'Использовать отправителя по умолчанию для всех писем',
                        'type' => 'checkbox',
                        'code' => MailOption::OPTION_MAIL_SENDER_ACTIVE,
                        'default' => 'N',
                        'values' => [
                            'N','Y',
                        ],
                    ],
                    [
                        'label' => 'Отправитель по умолчанию для всех писем',
                        'code' => MailOption::OPTION_MAIL_SENDER,
                        'attrs' => 'size="80"',
                    ],
                    [
                        'type' => 'header',
                        'label' => 'Блокировка отправки почты',
                    ],
                    [
                        'label' => 'Полностью заблокировать отправку почты',
                        'type' => 'checkbox',
                        'code' => MailOption::OPTION_MAIL_BLOCKING_ALL,
                        'default' => 'Y',
                        'values' => [
                            'N','Y',
                        ],
                    ],
                    [
                        'type' => 'note',
                        'label' => '<span class="required">Будьте осторожны!</span> Данная установка полностью блокирует отправку почты.',
                    ],
                    [
                        'type' => 'header',
                        'label' => 'Изменение получателя для всех писем',
                    ],
                    [
                        'label' => 'Включить отправку всех писем на указанную почту',
                        'type' => 'checkbox',
                        'code' => MailOption::OPTION_MAIL_TO_EXACT_ACTIVE,
                        'default' => 'N',
                        'values' => [
                            'N','Y',
                        ],
                    ],
                    [
                        'label' => 'Отправлять все письма только на указанную почту',
                        'type' => 'text',
                        'code' => MailOption::OPTION_MAIL_TO_EXACT,
                        'attrs' => 'size="80"',
                    ],
                    [
                        'type' => 'note',
                        'label' => '<span class="required">Будьте осторожны!</span> Если активировать данную настройку, то все письма будут отправляться только на указанную почту, вместо реального адресата. Реальный адресат письмо не получит.',
                    ],
                    [
                        'type' => 'header',
                        'label' => 'Органичения списка получателей',
                    ],
                    [
                        'label' => 'Включить ограничения для списка получателей',
                        'type' => 'checkbox',
                        'code' => MailOption::OPTION_MAIL_TOS_RESTRICT_ACTIVE,
                        'default' => 'N',
                        'values' => [
                            'N','Y',
                        ],
                    ],
                    [
                        'label' => 'Разрешённые получатели',
                        'code' => MailOption::OPTION_MAIL_TOS_RESTRICT,
                        'type' => 'textarea',
                        'attrs' => 'cols="80" rows="20"',
                    ],
                    [
                        'type' => 'note',
                        'label' => '<span class="required">Будьте осторожны!</span> Если активировать данную настройку, то все письма будут отправляться только на адреса, указанные в списке разрешённых получателей. Все остальные отправления будут заблокированы.',
                    ],
                ],
            ],
            [
                "DIV" => MailOption::OPTION_GROUP_SMTP,
                "TAB" => "Настройки SMTP",
                "ICON" => "main_settings",
                "TITLE" => "Настройки SMTP",
                'rows' => [
                    [
                        'type' => 'header',
                        'label' => 'Активность SMTP',
                    ],
                    [
                        'label' => 'Активность отправки SMTP',
                        'code' => MailOption::OPTION_SMTP_ACTIVE,
                        'default' => 'N',
                        'type' => 'checkbox',
                        'values' => [
                            'N','Y',
                        ],
                    ],
                    [
                        'type' => 'header',
                        'label' => 'Настройки SMTP',
                    ],
                    [
                        'label' => 'Хост',
                        'code' => MailOption::OPTION_SMTP_HOST,
                        'default' => MailOption::DEFAULT_HOST,
                        'attrs' => 'size="80"',
                    ],
                    [
                        'label' => 'Порт',
                        'code' => MailOption::OPTION_SMTP_PORT,
                        'default' => MailOption::DEFAULT_PORT,
                        'attrs' => 'size="80"',
                    ],
                    [
                        'label' => 'Логин',
                        'code' => MailOption::OPTION_SMTP_USERNAME,
                        'attrs' => 'size="80"',
                    ],
                    [
                        'label' => 'Пароль',
                        'code' => MailOption::OPTION_SMTP_PASSWORD,
                        'type' => 'password',
                        'attrs' => 'size="80"',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $tab
     * @param $data
     */
    protected function fillTabs(&$tab, $data)
    {
        if (array_key_exists($tab['DIV'], $data)) {
            $rowData = $data[$tab['DIV']];
            if (!empty($tab['rows'])) {
                foreach ($tab['rows'] as &$row) {
                    if (!empty($row['code']) && array_key_exists($row['code'], $rowData)) {
                        $row['value'] = $rowData[$row['code']];
                        if (is_callable($row['preload'])) {
                            $row['preload']($row, $tab);
                        }
                        $this->loadedOptions[$tab['DIV']][$row['code']] = &$row;
                    }
                }
            }
        }
    }

    /**
     * @param $tab
     * @param $data
     */
    protected function fillFilesTabs(&$tab, $data)
    {
        if (array_key_exists($tab['DIV'], $data)) {
            $rowData = $data[$tab['DIV']];
            if (!empty($tab['rows'])) {
                foreach ($tab['rows'] as &$row) {
                    if (!empty($row['code']) && array_key_exists($row['code'], $rowData['name'])) {
                        $fileData = [];
                        foreach ($rowData as $key => $val) {
                            $fileData[$key] = $val[$row['code']];
                        }
                        $row['file'] = $fileData;
                        $this->loadedOptions[$tab['DIV']][$row['code']] = &$row;
                    }
                }
            }
        }
    }

    /**
     * @param string $div
     * @param string $code
     * @param mixed $default
     * @return mixed
     */
    public function getOption($div, $code, $default=null)
    {
        return $this->getLoadedOptions()[$div][$code]['value'] ?? $this->getLoadedOptions()[$div][$code]['file'] ?? $default;
    }

    /**
     * @param string $div
     * @param mixed $default
     * @return mixed
     */
    public function getOptions($div, $default=null)
    {
        return $this->getLoadedOptions()[$div] ?? $default;
    }

    /**
     * @throws ArgumentOutOfRangeException
     */
    protected function saveOptions()
    {
        foreach ($this->tabs as $tab) {
            $fields = [];
            if (!empty($tab['rows'])) {
                foreach ($tab['rows'] as $row) {
                    if (!empty($row['code'])) {
                        if (is_callable($row['preSave'])) {
                            $row['preSave']($row, $tab, $fields);
                        }
                        if (array_key_exists('value', $row)) {
                            $fields[$row['code']] = $row['value'];
                        }
                    }
                }
            }
            $this->saveOption($tab, $fields);
        }
    }

    /**
     * @param $tab
     * @param $fields
     * @throws ArgumentOutOfRangeException
     */
    protected function saveOption($tab, $fields)
    {
        Option::set(self::MODULE_ID, $tab['DIV'], json_encode($fields, JSON_UNESCAPED_UNICODE));
    }
    /**
     *
     */
    public function loadOptions()
    {
        try {
            foreach ($this->tabs as &$tab) {
                $fields = Option::get(self::MODULE_ID, $tab['DIV'], []);
                if (is_string($fields)) {
                    $fields = json_decode($fields, true);
                }
                $this->fillTabs($tab, [$tab['DIV'] => $fields]);
            }
        } catch (\Throwable $throwable) {

        }
    }

    /**
     *
     */
    public function runActions()
    {
        global $USER;
        try {
            $this->loadOptions();
            if ($_SERVER["REQUEST_METHOD"]==="POST" && ($USER->CanDoOperation('edit_other_settings') && $USER->CanDoOperation('edit_groups')) && check_bitrix_sessid()) {
                if(strlen($_POST["Update"])>0) {
                    foreach ($this->tabs as &$tab) {
                        $this->fillTabs($tab, $_POST);
                        $this->fillFilesTabs($tab, $_FILES);
                    }
                    $this->saveOptions();
                    if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "") {
                        LocalRedirect($_REQUEST["back_url_settings"]);
                    } else {
                        LocalRedirect("/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=".urlencode(self::MODULE_ID)."&tabControl_active_tab=".urlencode($_REQUEST["tabControl_active_tab"])."&back_url_settings=".urlencode($_REQUEST["back_url_settings"]));
                    }
                }
            }
            $this->getModuleOptionsActions()->runActions();
        } catch (\Throwable $throwable) {

        }
    }
    /**
     * ModuleOptions constructor.
     */
    public function __construct()
    {
        $this->loadTabs();

        $this->htmlOptions = new HtmlOptions();
        $this->htmlOptions->setModuleOptions($this);

        $this->moduleOptionsActions = new ModuleOptionsActions();
        $this->moduleOptionsActions->setModuleOptions($this);
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * @return string
     */
    public function showForm()
    {
        $this->loadOptions();
        $this->getHtmlOptions()->render();
    }

    /**
     * @return HtmlOptions
     */
    protected function getHtmlOptions(): HtmlOptions
    {
        return $this->htmlOptions;
    }

    /**
     * @return ModuleOptionsActions
     */
    public function getModuleOptionsActions(): ModuleOptionsActions
    {
        return $this->moduleOptionsActions;
    }

    /**
     * @param bool $forceUpdate
     * @return array
     */
    public function getLoadedOptions($forceUpdate = false)
    {
        if ($forceUpdate || empty($this->loadedOptions)) {
            $this->loadOptions();
        }
        return $this->loadedOptions;
    }
}