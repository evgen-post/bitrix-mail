<?php
namespace Bx\Mail;

use CEventLog;

/**
 * Class MailLogger
 * @package Bx\Mail
 */
class MailLogger
{
    /**
     * @param mixed ...$args
     */
    public function log(...$args)
    {
        foreach ($args as $arg) {
            file_put_contents(__DIR__.'/custom_mail.txt', print_r($arg, true)."\n", FILE_APPEND);
        }
    }

    /**
     * @param $message
     * @param string $itemId
     */
    public function eventLog($message, $itemId="MESSAGE")
    {
        CEventLog::Log(CEventLog::SEVERITY_ERROR, MailOption::AUDIT_TYPE_ID, MailOption::MODULE_ID, $itemId, $message);
    }
}