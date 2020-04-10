<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

/**
 * Class Option
 * @package Bx\Mail
 */
class MailOption
{
    const MODULE_ID = 'bx.mail';
    const AUDIT_TYPE_ID = 'PHP Sender';

    const OPTION_ACTIVE = 'SMTP_ACTIVE';
    const OPTION_HOST = 'SMTP_HOST';
    const OPTION_USERNAME = 'SMTP_USERNAME';
    const OPTION_PASSWORD = 'SMTP_PASSWORD';
    const OPTION_PORT = 'SMTP_PORT';
    const OPTION_SENDER = 'SMTP_SENDER';
    const OPTION_ALLOW_SENDERS = 'MAIL_SENDERS';
    const OPTION_MAIL_SENDERS_STRICT = 'MAIL_SENDERS_STRICT';

    const DEFAULT_HOST = '';
    const DEFAULT_PORT = 587;
    const DEFAULT_USERNAME = '';
    const DEFAULT_PASSWORD = '';

    const OPTION_FIELDS = [
        self::OPTION_ACTIVE,
        self::OPTION_HOST,
        self::OPTION_PORT,
        self::OPTION_USERNAME,
        self::OPTION_PASSWORD,
        self::OPTION_SENDER,
        self::OPTION_ALLOW_SENDERS,
        self::OPTION_MAIL_SENDERS_STRICT,
    ];

    protected $active;
    protected $host;
    protected $userName;
    protected $password;
    protected $port;
    protected $sender;
    protected $isStrict;
    protected $allowedEmails;

    /**
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getAllowedEmails()
    {
        if ($this->isStrict()) {
            if (is_null($this->allowedEmails)) {
                $this->allowedEmails = array_map('trim', preg_split("([\n,;]+)ui", Option::get(MailOption::MODULE_ID, MailOption::OPTION_ALLOW_SENDERS)));
            }
            return $this->allowedEmails;
        }
        return [];
    }
    /**
     * @param $fields
     * @throws ArgumentOutOfRangeException
     */
    public static function saveOptions($fields)
    {
        foreach (self::OPTION_FIELDS as $fieldName) {
            if (array_key_exists($fieldName, $fields)) {
                Option::set(self::MODULE_ID, $fieldName, $fields[$fieldName]);
            }
        }
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function isActive()
    {
        if (!isset($this->active)) {
            $this->active = Option::get(self::MODULE_ID, self::OPTION_ACTIVE, 'N') === 'Y';
        }
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
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getHost()
    {
        if (!isset($this->host)) {
            $this->host = Option::get(self::MODULE_ID, self::OPTION_HOST, self::DEFAULT_HOST);
        }

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
            $this->sender = Option::get(self::MODULE_ID, self::OPTION_SENDER);
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
            $this->userName = Option::get(self::MODULE_ID, self::OPTION_USERNAME, self::DEFAULT_USERNAME);
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
            $this->password = Option::get(self::MODULE_ID, self::OPTION_PASSWORD, self::DEFAULT_PASSWORD);
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
            $this->port = Option::get(self::MODULE_ID, self::OPTION_PORT, self::DEFAULT_PORT);
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
            $this->isStrict = (Option::get(MailOption::MODULE_ID, MailOption::OPTION_MAIL_SENDERS_STRICT, 'N') === 'Y');
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
}