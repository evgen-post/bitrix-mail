<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use CModule;

/**
 * Class Option
 * @package Bx\Mail
 */
class MailOption
{
    const MODULE_ID = 'bx.mail';
    const AUDIT_TYPE_ID = 'PHP Sender';

    const OPTION_SMTP_ACTIVE = 'SMTP_ACTIVE';
    const OPTION_SMTP_HOST = 'SMTP_HOST';
    const OPTION_SMTP_USERNAME = 'SMTP_USERNAME';
    const OPTION_SMTP_PASSWORD = 'SMTP_PASSWORD';
    const OPTION_SMTP_PORT = 'SMTP_PORT';

    const OPTION_MAIL_SENDER_ACTIVE = 'MAIL_SENDER_ACTIVE';
    const OPTION_MAIL_SENDER = 'MAIL_SENDER';

    const OPTION_MAIL_ALLOW_SENDERS = 'MAIL_ALLOW_SENDERS';
    const OPTION_MAIL_SENDERS_RESTRICT = 'MAIL_SENDERS_RESTRICT';

    const OPTION_MAIL_TO_EXACT_ACTIVE = 'MAIL_TO_ACTIVE';
    const OPTION_MAIL_TO_EXACT = 'MAIL_TO_EXACT';

    const OPTION_MAIL_TOS_RESTRICT_ACTIVE = 'MAIL_TOS_RESTRICT_ACTIVE';
    const OPTION_MAIL_TOS_RESTRICT = 'MAIL_TOS_RESTRICT';

    const OPTION_MAIL_BLOCKING_ALL = 'MAIL_BLOCKING_ALL';

    const OPTION_GROUP_MAIL = 'MAIL';
    const OPTION_GROUP_SMTP = 'SMTP';
    const OPTION_GROUP_LOGS = 'LOGS';

    const DEFAULT_HOST = '';
    const DEFAULT_PORT = 587;
    const DEFAULT_USERNAME = '';
    const DEFAULT_PASSWORD = '';

    protected static $tabs = [
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
                    'default' => 'N',
                    'values' => [
                        'N','Y',
                    ],
                ],
                [
                    'type' => 'info',
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
                    'type' => 'info',
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
                    'type' => 'info',
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
        [
            "DIV" => MailOption::OPTION_GROUP_LOGS,
            "TAB" => "Лорирование",
            "ICON" => "main_settings",
            "TITLE" => "Логирование",
            'rows' => [
                [
                    'type' => 'header',
                    'label' => 'Настройки SMTP',
                ],
            ],
        ],
    ];
    protected static $isLoadedOptions = false;
    protected $active;
    protected $host;
    protected $userName;
    protected $password;
    protected $port;
    protected $sender;
    protected $isStrict;
    protected $allowedEmails;

    /**
     * @return bool
     */
    public function isMailSenderActive()
    {
        self::loadOptionsSoft();
        return self::getTabs()[self::OPTION_GROUP_MAIL][self::OPTION_MAIL_SENDER_ACTIVE] === 'Y';
    }

    /**
     * @return bool
     */
    public function isMailToExactActive()
    {
        self::loadOptionsSoft();
        return self::getTabs()[self::OPTION_GROUP_MAIL][self::OPTION_MAIL_TO_EXACT_ACTIVE] === 'Y';
    }

    /**
     * @return bool
     */
    public function isMailBlockingAll()
    {
        self::loadOptionsSoft();
        return self::getTabs()[self::OPTION_GROUP_MAIL][self::OPTION_MAIL_BLOCKING_ALL] === 'Y';
    }

    /**
     * @return mixed
     */
    public function getMailToExact()
    {
        self::loadOptionsSoft();
        return self::getTabs()[self::OPTION_GROUP_MAIL][self::OPTION_MAIL_TO_EXACT];
    }

    /**
     * @return mixed
     */
    public function getMailSender()
    {
        self::loadOptionsSoft();
        return self::getTabs()[self::OPTION_GROUP_MAIL][self::OPTION_MAIL_SENDER];
    }

    /**
     *
     */
    public static function installModule()
    {
        try {
            $_SERVER['DOCUMENT_ROOT'] = rtrim(__DIR__.'/../../../../', '/');
            $prologBeforeFileName = '/bitrix/modules/main/include/prolog_before.php';
            $prologBeforeFileFullPath = $_SERVER['DOCUMENT_ROOT'].$prologBeforeFileName;
            if (!file_exists($prologBeforeFileFullPath)) {
                throw new \Exception(sprintf('Не найден файл 1с-битрикс - "%s"', $prologBeforeFileName));
            }
            require_once $prologBeforeFileFullPath;
            if (!ModuleManager::isModuleInstalled(self::MODULE_ID)) {
                if ($ob = CModule::CreateModuleObject(self::MODULE_ID)) {
                    $ob->DoInstall();
                }
            }
        } catch (\Throwable $throwable) {

        }
    }

    /**
     * @return mixed
     */
    public function getAllowedEmails()
    {
        return $this->allowedEmails;
    }
    /**
     * @param $fields
     * @throws ArgumentOutOfRangeException
     */
    public static function saveOptions($fields)
    {
        foreach (self::getTabs() as $tab) {
            $config = [];
            if (!empty($tab['rows'])) {
                foreach ($tab['rows'] as $row) {
                    if (!empty($row['code'])) {
                        $config[$row['code']] = $fields[$tab['DIV']][$row['code']];
                    }
                }
            }
            if (!empty($config)) {
                Option::set(self::MODULE_ID, $tab['DIV'], json_encode($config, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     *
     */
    protected static function loadOptionsSoft()
    {
        try {
            if (!MailOption::$isLoadedOptions) {
                MailOption::loadOptions();
            }
        } catch (\Throwable $throwable) {

        }

    }
    /**
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function loadOptions()
    {
        foreach (self::$tabs as &$tab) {
            if (!empty($tab['rows'])) {
                $config = Option::get(self::MODULE_ID, $tab['DIV'], []);
                if (!is_array($config)) {
                    try {
                        $config = json_decode($config, true);
                    } catch (\Throwable $throwable) {
                        $config = [];
                    }
                }
                foreach ($tab['rows'] as &$row) {
                    if (array_key_exists($row['code'], $config)) {
                        $row['value'] = $config[$row['code']];
                    } elseif (array_key_exists('default', $row)) {
                        $row['value'] = $row['default'];
                    }
                }
            }
        }
        self::$isLoadedOptions = true;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return MailOption
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * @param mixed $host
     * @return MailOption
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function getSender()
    {
        if (!isset($this->sender)) {
            $this->sender = Option::get(self::MODULE_ID, self::OPTION_MAIL_SENDER);
        }
        return $this->sender;
    }

    /**
     * @param mixed $sender
     * @return MailOption
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getUserName()
    {
        if (!isset($this->userName)) {
            $this->userName = Option::get(self::MODULE_ID, self::OPTION_SMTP_USERNAME, self::DEFAULT_USERNAME);
        }

        return $this->userName;
    }

    /**
     * @param mixed $userName
     * @return MailOption
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getPassword()
    {
        if (!isset($this->password)) {
            $this->password = Option::get(self::MODULE_ID, self::OPTION_SMTP_PASSWORD, self::DEFAULT_PASSWORD);
        }

        return $this->password;
    }

    /**
     * @param mixed $password
     * @return MailOption
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getPort()
    {
        if (!isset($this->port)) {
            $this->port = Option::get(self::MODULE_ID, self::OPTION_SMTP_PORT, self::DEFAULT_PORT);
        }

        return $this->port;
    }

    /**
     * @param mixed $port
     * @return MailOption
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function isStrict()
    {
        if (is_null($this->isStrict)) {
            $this->isStrict = (Option::get(MailOption::MODULE_ID, MailOption::OPTION_MAIL_SENDERS_RESTRICT, 'N') === 'Y');
        }

        return $this->isStrict;
    }

    /**
     * @param bool $isStrict
     * @return MailOption
     */
    public function setIsStrict($isStrict)
    {
        $this->isStrict = $isStrict;
        return $this;
    }

    /**
     * @return CustomMailAdapter
     */
    public function getAdapter()
    {
        return CustomMailAdapter::getInstance();
    }

    /**
     * @return array
     */
    public static function getTabs()
    {
        return self::$tabs;
    }

}