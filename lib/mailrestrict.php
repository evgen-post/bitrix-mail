<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
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
     * @param $email
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function checkAllowEmail($email)
    {
        $emails = $this->getAdapter()->getOptions()->getAllowedEmails();
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
                $this->getAdapter()->getLogger()->eventLog($params['TO'].' BLOCKED', 'EMAIL');
                return new EventResult(EventResult::ERROR);
            }
        });
    }

    /**
     * @return CustomMailAdapter
     */
    public function getAdapter()
    {
        return CustomMailAdapter::getInstance();
    }

}