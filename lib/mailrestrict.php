<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;

/**
 * Class MailRestrict
 * @package Bx\Mail
 */
class MailRestrict
{

    /**
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    protected function getAllowedEmails()
    {
        if (is_null($this->isStrict)) {
            $this->isStrict = (Option::get(self::MODULE_ID, self::OPTION_MAIL_SENDERS_STRICT, 'N') === 'Y');
        }
        if ($this->isStrict) {
            if (is_null($this->allowedEmails)) {
                $this->allowedEmails = array_map('trim', preg_split("([\n,;]+)ui", Option::get(self::MODULE_ID, self::OPTION_ALLOW_SENDERS)));
            }
            return $this->allowedEmails;
        }
        return [];
    }

    /**
     * @param $email
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function checkAllowEmail($email)
    {
        $emails = $this->getAllowedEmails();
        return empty($emails) || in_array($email, $emails);
    }

    /**
     *
     */
    public function onlyEmailTo()
    {
        EventManager::getInstance()->addEventHandler('main', 'OnBeforeMailSend', function (Event $event) {
            $params = $event->getParameter(0);
            if (!$this->checkAllowEmail($params['TO'])) {
                self::log($params['TO'].' BLOCKED');
                return new EventResult(EventResult::ERROR);
            }
        });
    }
}