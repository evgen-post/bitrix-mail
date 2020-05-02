<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bx\Mail\Options\ModuleOptions;
use CModule;

/**
 * Class Option
 * @package Bx\Mail
 */
class MailOption
{
    const MODULE_ID = ModuleOptions::MODULE_ID;
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
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    protected $tabs = [];
    protected $host;
    protected $userName;
    protected $password;
    protected $port;
    protected $sender;
    protected $isStrict;
    protected $allowedEmails;

    /**
     * MailOption constructor.
     */
    public function __construct()
    {
        try {
            $this->moduleOptions = new ModuleOptions();
            $this->loadOptions();
        } catch (\Throwable $throwable) {

        }
    }

    /**
     * @return bool
     */
    public function isMailSenderActive()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_SENDER_ACTIVE, 'N')['value'] === 'Y';
    }

    /**
     * @return bool
     */
    public function isMailToExactActive()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_TO_EXACT_ACTIVE, 'N')['value'] === 'Y';
    }

    /**
     * @return bool
     */
    public function isMailBlockingAll()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_BLOCKING_ALL, 'N')['value'] === 'Y';
    }

    /**
     * @return mixed
     */
    public function getMailToExact()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_TO_EXACT)['value'];
    }

    /**
     * @return mixed
     */
    public function getMailSender()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_SENDER)['value'];
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
        $isRestricted = $this->getModuleOptions()->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_TOS_RESTRICT_ACTIVE, "N") === 'Y';
        $allowedEmails = [];
        if ($isRestricted) {
            $allowedEmails = $this->getModuleOptions()->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_TOS_RESTRICT, []);
            if (!empty($allowedEmails) && is_string($allowedEmails)) {
                $allowedEmails = array_filter(preg_split('([\s,;]+)ui', $allowedEmails));
            }
        }
        return $allowedEmails;
    }

    /**
     */
    public function loadOptions()
    {
        $this->getModuleOptions()->loadOptions();
    }

    /**
     * @param $div
     * @param $code
     * @param null $default
     * @return mixed|null
     */
    public function getOption($div, $code, $default=null)
    {
        return $this->getModuleOptions()->getOption($div, $code, $default);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getOption(self::OPTION_GROUP_SMTP, self::OPTION_SMTP_ACTIVE, 'N')==='Y';
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->getOption(self::OPTION_GROUP_SMTP, self::OPTION_SMTP_HOST, self::DEFAULT_HOST);
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_SENDER);
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->getOption(self::OPTION_GROUP_SMTP, self::OPTION_SMTP_USERNAME, self::DEFAULT_USERNAME);
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->getOption(self::OPTION_GROUP_SMTP, self::OPTION_SMTP_PASSWORD, self::DEFAULT_PASSWORD);
    }

    /**
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getPort()
    {
        return $this->getOption(self::OPTION_GROUP_SMTP, self::OPTION_SMTP_PORT, self::DEFAULT_PORT);
    }

    /**
     * @return bool
     */
    public function isStrict()
    {
        return $this->getOption(self::OPTION_GROUP_MAIL, self::OPTION_MAIL_SENDERS_RESTRICT, 'N');
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

}