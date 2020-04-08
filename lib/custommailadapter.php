<?php
namespace Bx\Mail;
use Bitrix\Main\ArgumentOutOfRangeException;
use CEventLog;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class CustomMailAdapter
 * @package Bx\Mail
 */
class CustomMailAdapter
{

    protected $sender;
    protected $errors = [];
    protected $additionalHeaders = [];
    protected $boundary;
    protected $textMessage = '';
    protected $htmlMessage = '';
    protected $isHtml = true;
    protected $contentType;
    protected $allowedEmails;
    protected $isStrict;
    /**
     * @var PHPMailer
     */
    public $phpMailer;
    /**
     * @var CustomMailAdapter
     */
    protected static $instance;
    /**
     * @var MailOption
     */
    protected $options;
    /**
     * @var MailParser
     */
    protected $parser;
    /**
     * @var MailRestrict
     */
    protected $restrict;
    /**
     * @var MailLogger
     */
    protected $logger;

    /**
     * CustomMailAdapter constructor.
     */
    protected function __construct()
    {
        $this->options = new MailOption();
        $this->parser = new MailParser();
        $this->restrict = new MailRestrict();
        $this->logger = new MailLogger();
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            $this->phpMailer = new PHPMailer();
        }
    }

    /**
     * @return CustomMailAdapter
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     */
    public function createCustomMailFunction()
    {
        if ($this->canCreateFunctionCustomMail()) {
            include_once __DIR__.'/custom_mail.php';
        }
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param $additional_headers
     * @param $additional_parameters
     * @param $context
     * @return bool
     */
    public function send($to, $subject, $message, $additional_headers, $additional_parameters, $context)
    {
        if ($this->canCreateFunctionCustomMail()) {
            try {
                $this->getParser()->parseAdditionalHeaders($additional_headers);
                $address = $this->getParser()->getSenderAddress();
                if ($address) {
                    $this->getPhpMailer()->setFrom($address['email'], $address['name']);
                } else {
                    throw new Exception('Не указан отправитель.');
                }
                foreach ($this->getParser()->getRetryToAddresses() as $address) {
                    $this->getPhpMailer()->addReplyTo($address['email'], $address['name']);
                }
                foreach ($this->getParser()->getBCCAddresses() as $address) {
                    $this->getPhpMailer()->addBCC($address['email'], $address['name']);
                }
                foreach ($this->getParser()->getAdditionalHeaders() as $headerKey => $header) {
                    $this->getPhpMailer()->addCustomHeader($headerKey, $header);
                }
                foreach ($this->getParser()->parseAddresses($to) as $email) {
                    if ($address = $this->getParser()->parseAddress($email)) {
                        $this->getPhpMailer()->addAddress($address['email'], $address['name']);
                    }
                }
                $this->getParser()->parseMessage($message);
//                $this->phpMailer->sign('/usr/lib/ssl/certs/cacert.pem', '/usr/lib/ssl/private/cakey.pem','');
                $this->getPhpMailer()->SMTPDebug = SMTP::DEBUG_OFF;
//                $this->phpMailer->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
//                $this->phpMailer->Debugoutput = function (...$args){
//                    self::log(...$args);
//                };
                $this->getPhpMailer()->isSMTP();

                $this->getPhpMailer()->Host = $this->getOptions()->getHost();
                $this->getPhpMailer()->Username = $this->getOptions()->getUserName();
                $this->getPhpMailer()->Password = $this->getOptions()->getPassword();
                $this->getPhpMailer()->Port = $this->getOptions()->getPort();

                $this->getPhpMailer()->CharSet = PHPMailer::CHARSET_UTF8;
                $this->getPhpMailer()->SMTPAuth = true;
                $this->getPhpMailer()->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->getPhpMailer()->Subject = $subject;
                $this->getPhpMailer()->Body = $this->getParser()->getHtmlMessage();
                if ($this->getParser()->isHtml()) {
                    $this->getPhpMailer()->AltBody = $this->getParser()->getTextMessage();
                }
                $this->getPhpMailer()->isHTML($this->getParser()->isHtml());
                if (!$this->getPhpMailer()->send()) {
                    $this->getLogger()->eventLog('Не удалось доставить почту');
                    return false;
                }
                return true;
            } catch (Exception $e) {
                $this->getLogger()->eventLog($e->getMessage());
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function includeVendorFiles()
    {
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')
            && class_exists('\PHPMailer\PHPMailer\SMTP')
            && class_exists('\PHPMailer\PHPMailer\Exception')
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * @param $error
     * @return $this
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return bool
     */
    protected function canCreateFunctionCustomMail()
    {
        try {
            return $this->checkFunctionNotExists() && $this->isActive() && $this->checkFields() && $this->includeVendorFiles();
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function checkFunctionNotExists()
    {
        if (function_exists('custom_mail')) {
            $this->addError('Фукнция custom_mail уже существует');
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return MailOption
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return MailParser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @return MailRestrict
     */
    public function getRestrict()
    {
        return $this->restrict;
    }

    /**
     * @return MailLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return PHPMailer
     */
    public function getPhpMailer()
    {
        return $this->phpMailer;
    }

    /**
     * @return bool
     */
    protected function checkFields()
    {
        if (empty($this->getHost())) {
            $this->addError('Host not specified');
        }
        if (empty($this->getPort())) {
            $this->addError('Port not specified');
        }
        if (empty($this->getPassword())) {
            $this->addError('Password not specified');
        }
        if (empty($this->getUserName())) {
            $this->addError('User name not specified');
        }
        return !$this->hasErrors();
    }
}