<?php
namespace Bx\Mail;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use CEventLog;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class CustomMailAdapter
{
    const MODULE_ID = 'pz.smtp';

    const OPTION_ACTIVE = 'SMTP_ACTIVE';
    const OPTION_HOST = 'SMTP_HOST';
    const OPTION_USERNAME = 'SMTP_USERNAME';
    const OPTION_PASSWORD = 'SMTP_PASSWORD';
    const OPTION_PORT = 'SMTP_PORT';
    const OPTION_SENDER = 'SMTP_SENDER';
    const OPTION_ALLOW_SENDERS = 'MAIL_SENDERS';
    const OPTION_MAIL_SENDERS_STRICT = 'MAIL_SENDERS_STRICT';

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

    const DEFAULT_HOST = 'mail.telecomsky.ru';
    const DEFAULT_PORT = 587;
    const DEFAULT_USERNAME = '';
    const DEFAULT_PASSWORD = '';

    protected $testEmail = '';
    protected $active;
    protected $host;
    protected $userName;
    protected $password;
    protected $port;
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
     * CustomMailAdapter constructor.
     */
    protected function __construct()
    {

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
    protected function checkAllowEmail($email)
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
     * @param $name
     * @return mixed
     */
    protected function getAdditionalHeader($name)
    {
        if (array_key_exists($name, $this->additionalHeaders)) {
            return $this->additionalHeaders[$name];
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function shiftAdditionalHeader($name)
    {
        if (array_key_exists($name, $this->additionalHeaders)) {
            $value = $this->additionalHeaders[$name];
            unset($this->additionalHeaders[$name]);
            return $value;
        }
    }

    /**
     * @param $headers
     * @return array
     */
    protected function parseHeaders($headers)
    {
        $result = [];
        if (is_string($headers) && !empty($headers)) {
            $headers = array_map('trim', preg_split("([\r\n])ui", $headers));
            foreach ($headers as $key => $header) {
                $header = array_map('trim', explode(':', $header, 2));
                $result[$header[0]] = $header[1];
            }
        }
        return $result;
    }
    /**
     * @param $additional_headers
     */
    protected function parseAdditionalHeaders($additional_headers)
    {
        $headers = $this->parseHeaders($additional_headers);
        foreach ($headers as $key => $header) {
            $this->additionalHeaders[$key] = $header;
        }
    }

    /**
     * @param mixed ...$args
     */
    protected function log(...$args)
    {
        foreach ($args as $arg) {
//            file_put_contents(__DIR__.'/custom_mail.txt', '<<< '.date('c')." >>>\n", FILE_APPEND);
            file_put_contents(__DIR__.'/custom_mail.txt', print_r($arg, true)."\n", FILE_APPEND);
        }
    }
    /**
     * @param $message
     * @param $boundary
     * @return array
     */
    protected function parseMessageWithBoundary($message, $boundary)
    {
        if (stripos($message, $boundary) !== false) {
            return array_values(array_map('trim', array_filter(explode('--'.$boundary, substr(trim($message), 0, -2)))));
        }
        return [];
    }

    /**
     * @param $message
     * @return array
     */
    protected function parseMessageWithHeaders($message)
    {
        $result = [
            'headers' => [],
            'content' => '',
        ];
        if (!empty($message) && is_string($message)) {
            $row = preg_split("([\r\n]{2})ui", $message, 2);
            if (!empty($row)) {
                $result['headers'] = $this->parseHeaders($row[0]);
                $result['content'] = $row[1];
            }
        }
        return $result;
    }

    /**
     * @param $message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function parseMessage($message)
    {
        if (!empty($this->getBoundary())) {
            $arMessage = $this->parseMessageWithBoundary($message, $this->getBoundary());
            if (!empty($arMessage)) {
                foreach ($arMessage as $index => $row) {
                    $row = $this->parseMessageWithHeaders($row);
                    if (!empty($row['headers']['Content-Disposition']) && stripos($row['headers']['Content-Disposition'], 'attachment') !== false) {
                        $attachment = [];
                        foreach ($row['headers'] as $rowKey => $rowHeader) {
                            if ($rowKey === 'Content-Type') {
                                $attachment['type'] = $rowHeader;
                                if (preg_match('(name\="([^"]*)")ui', $attachment['type'], $m)) {
                                    $attachment['name'] = $m[1];
                                }
                            } elseif ($rowKey === 'Content-Disposition') {
                                $attachment['disposition'] = $rowHeader;
                            } elseif ($rowKey === 'Content-Transfer-Encoding') {
                                $attachment['encoding'] = $rowHeader;
                            }
                        }
                        $this->phpMailer->addStringAttachment(base64_decode($row['content']), $attachment['name'],
                            $attachment['encoding'], $attachment['type']);
                    } elseif (!empty($row['headers']['Content-Type'])
                        && preg_match('(multipart/alternative; boundary="([^"]+)")ui', $row['headers']['Content-Type'], $m)
                    ) {
                        $boundary = $m[1];
                        if (stripos($row['content'], $boundary) !== false) {
                            $arSubMessage = $this->parseMessageWithBoundary($row['content'], $boundary);
                            foreach ($arSubMessage as $item) {
                                $item = $this->parseMessageWithHeaders($item);
                                if (stripos($item['headers']['Content-Type'], 'text/plain') !== false) {
                                    $this->setTextMessage($item['content']);
                                } elseif (stripos($item['headers']['Content-Type'], 'text/html') !== false) {
                                    $this->setHtmlMessage($item['content']);
                                }
                            }
                        }
                    } else {
                        if (stripos($row['headers']['Content-Type'], 'text/plain') !== false ) {
                            $this->setIsHtml(false);
                        }
                        $this->setHtmlMessage($row['content']);
                        $this->setTextMessage(strip_tags($this->htmlMessage));
                    }
                }
            }
        } else {
            if (stripos($this->contentType, 'text/plain') !== false) {
                $this->setIsHtml(false);
                $this->setHtmlMessage($message);
            } elseif (stripos($this->contentType, 'text/html') !== false) {
                $this->setHtmlMessage($message);
                $this->setTextMessage(strip_tags($this->getHtmlMessage()));
            }
        }
    }

    /**
     * @param $addresses
     * @return array
     */
    protected function parseAddresses($addresses)
    {
        if (!empty($addresses) && is_string($addresses)) {
            return array_map('trim', preg_split("([\n,;]+)ui", $addresses));
        }
        return [];
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
            $this->phpMailer = new PHPMailer(true);
            try {

                $this->parseAdditionalHeaders($additional_headers);

                if ($value = $this->shiftAdditionalHeader('From')) {
                    if (!empty($this->getSender())) {
                        $address = $this->parseAddress($this->getSender(), true);
                    } else {
                        $address = $this->parseAddress($value, true);
                    }
                    if ($address) {
                        $this->phpMailer->setFrom($address['email'], $address['name']);
                    }
                }

                if ($value = $this->shiftAdditionalHeader('Reply-To')) {
                    foreach ($this->parseAddresses($value) as $email) {
                        if ($address = $this->parseAddress($email)) {
                            $this->phpMailer->addReplyTo($address['email'], $address['name']);
                        }
                    }
                }

                if ($value = $this->shiftAdditionalHeader('BCC')) {
                    foreach ($this->parseAddresses($value) as $email) {
                        if ($address = $this->parseAddress($email)) {
                            $this->phpMailer->addBCC($address['email'], $address['name']);
                        }
                    }
                }

                if ($value = $this->shiftAdditionalHeader('Content-Type')) {
                    $this->contentType = $value;
                    if (preg_match('(multipart/mixed; boundary="(.+?)")ui', $this->contentType, $m)) {
                        $this->setBoundary($m[1]);
                    }
                }

                foreach ($this->getAdditionalHeaders() as $headerKey => $header) {
                    $this->phpMailer->addCustomHeader($headerKey, $header);
                }

                $this->parseMessage($message);

//                $this->phpMailer->sign('/usr/lib/ssl/certs/cacert.pem', '/usr/lib/ssl/private/cakey.pem','');

                $this->phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
//                $this->phpMailer->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
//                $this->phpMailer->Debugoutput = function (...$args){
//                    self::log(...$args);
//                };
                $this->phpMailer->isSMTP();

                $this->phpMailer->Host = $this->getHost();
                $this->phpMailer->Username = $this->getUserName();
                $this->phpMailer->Password = $this->getPassword();
                $this->phpMailer->Port = $this->getPort();

                $this->phpMailer->CharSet = PHPMailer::CHARSET_UTF8;
                $this->phpMailer->SMTPAuth = true;
                $this->phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                foreach ($this->parseAddresses($to) as $email) {
                    if ($address = $this->parseAddress($email)) {
                        $this->phpMailer->addAddress($address['email'], $address['name']);
                    }
                }

                $this->phpMailer->Subject = $subject;
                $this->phpMailer->Body = $this->getHtmlMessage();

                if ($this->isHtml()) {
                    $this->phpMailer->AltBody = $this->getTextMessage();
                } else {
                    $this->phpMailer->isHTML(false);
                }
                if (!$this->phpMailer->send()) {
                    CEventLog::Log(CEventLog::SEVERITY_ERROR, 'MAIL', self::MODULE_ID, 'MESSAGE',
                        'Не удалось доставить почту');
                    return false;
                }
                return true;
            } catch (Exception $e) {
                CEventLog::Log(CEventLog::SEVERITY_ERROR, 'MAIL', self::MODULE_ID, 'MESSAGE', $e->getMessage());
            }
            return false;
        }
    }

    /**
     * @param $value
     * @param bool $isSender
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    protected function parseAddress($value, $isSender=false)
    {
        $result = [
            'name' => '',
            'email' => $value,
        ];
        if (preg_match('((.*?)\<([^>]+))ui', $value, $m)) {
            $result['name'] = trim($m[1]);
            $result['email'] = trim($m[2]);
        }
        if ($isSender || $this->checkAllowEmail($result['email'])) {
            return $result;
        }
    }
    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws ArgumentOutOfRangeException
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

    /**
     * @return bool
     */
    protected function includeVendorFiles()
    {
        foreach (['PHPMailer.php', 'SMTP.php', 'Exception.php',] as $fileName) {
            $file = __DIR__ . '/../../../../vendor/phpmailer/phpmailer/src/' . $fileName;
            if (!file_exists($file)) {
                CEventLog::Log(CEventLog::SEVERITY_ERROR, 'MAIL', self::MODULE_ID, 'MESSAGE',
                    'Не найдены файлы библиотеки phpmailer');
                return false;
            } else {
                include_once $file;
            }
        }
        return true;
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
            return !function_exists('custom_mail') && $this->isActive() && $this->checkFields() && $this->includeVendorFiles();
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function isActive()
    {
        if (!isset($this->active)) {
            $this->active = Option::get(self::MODULE_ID, self::OPTION_ACTIVE, 'Y') === 'Y';
        }
        return $this->active;
    }

    /**
     * @param bool $active
     * @return CustomMailAdapter
     * @throws ArgumentOutOfRangeException
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
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
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
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
     * @return CustomMailAdapter
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @param mixed $host
     * @return CustomMailAdapter
     * @throws ArgumentOutOfRangeException
     */
    public function setHost($host)
    {
        $this->host = $host;
        Option::set(self::MODULE_ID, self::OPTION_HOST, $this->host);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
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
     * @return CustomMailAdapter
     * @throws ArgumentOutOfRangeException
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        Option::set(self::MODULE_ID, self::OPTION_USERNAME, $this->userName);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
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
     * @return CustomMailAdapter
     * @throws ArgumentOutOfRangeException
     */
    public function setPassword($password)
    {
        $this->password = $password;
        Option::set(self::MODULE_ID, self::OPTION_PASSWORD, $this->password);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
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
     * @return CustomMailAdapter
     * @throws ArgumentOutOfRangeException
     */
    public function setPort($port)
    {
        $this->port = $port;
        Option::set(self::MODULE_ID, self::OPTION_PORT, $this->port);
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @param mixed $boundary
     * @return CustomMailAdapter
     */
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalHeaders()
    {
        return $this->additionalHeaders;
    }

    /**
     * @return mixed
     */
    public function getTextMessage()
    {
        return $this->textMessage;
    }

    /**
     * @param mixed $textMessage
     * @return CustomMailAdapter
     */
    public function setTextMessage($textMessage)
    {
        $this->textMessage = $textMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHtmlMessage()
    {
        return $this->htmlMessage;
    }

    /**
     * @param mixed $htmlMessage
     * @return CustomMailAdapter
     */
    public function setHtmlMessage($htmlMessage)
    {
        $this->htmlMessage = $htmlMessage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return $this->isHtml;
    }

    /**
     * @param bool $isHtml
     * @return CustomMailAdapter
     */
    public function setIsHtml($isHtml)
    {
        $this->isHtml = $isHtml;
        return $this;
    }

}