<?php
namespace Bx\Mail;

use Bitrix\Main\EventResult;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
/**
 * Class MailRestrict
 * @package Bx\Mail
 */
class MailRestrict
{

    /**
     * @param $email
     * @return bool
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
                $toExactEmail = $this->getAdapter()->getOptions()->getOption(MailOption::OPTION_GROUP_MAIL, MailOption::OPTION_MAIL_TO_EXACT, '');
                $isToExactEmailAllow = $this->getAdapter()
                    ->getOptions()
                    ->getOption(MailOption::OPTION_GROUP_MAIL, MailOption::OPTION_MAIL_TO_EXACT_ACTIVE, 'N') === 'Y';
                if ($isToExactEmailAllow && !empty($toExactEmail)) {
                    $this->getAdapter()->getLogger()->eventLog($params['TO'].' BLOCKED AND MAIL SEND TO '.$toExactEmail, 'EMAIL');
                    $params['TO'] = $toExactEmail;
                    return new EventResult(EventResult::SUCCESS, $params);
                }
                $this->getAdapter()->getLogger()->eventLog($params['TO'].' BLOCKED ', 'EMAIL');
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