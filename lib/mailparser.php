<?php
namespace Bx\Mail;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use PHPMailer\PHPMailer\Exception;

/**
 * Class MailParser
 * @package Bx\Mail
 */
class MailParser
{
    protected $additionalHeaders = [];
    protected $contentType;
    protected $boundary;
    protected $senderAddress;
    protected $replyToAddresses = [];
    protected $BCCAddresses = [];
    protected $CCAddresses = [];
    protected $isHtml = true;
    protected $htmlMessage;
    protected $textMessage;

    /**
     * @param $header
     */
    protected function parseHeaderContentType($header)
    {
        $this->setContentType($header);
        if (preg_match('(multipart/mixed; boundary="([^"]+)")ui', $this->getContentType(), $m)) {
            $this->setBoundary($m[1]);
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
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function parseAdditionalHeaders($additional_headers)
    {
        $headers = $this->parseHeaders($additional_headers);
        foreach ($headers as $key => $header) {
            if ($key === 'Content-Type') {
                $this->parseHeaderContentType($header);
                continue;
            } elseif ($key === 'From') {
                if ($address = $this->parseAddress($header, true)) {
                    $this->setSenderAddress($address);
                }
                continue;
            } elseif ($key === 'Reply-To') {
                foreach ($this->parseAddresses($header) as $email) {
                    if ($address = $this->parseAddress($email)) {
                        $this->addReplyToAddress($address);
                    }
                }
                continue;
            } elseif ($key === 'BCC') {
                foreach ($this->parseAddresses($header) as $email) {
                    if ($address = $this->parseAddress($email)) {
                        $this->addBCCAddress($address);
                    }
                }
                continue;
            } elseif ($key === 'CC') {
                foreach ($this->parseAddresses($header) as $email) {
                    if ($address = $this->parseAddress($email)) {
                        $this->addCCAddress($address);
                    }
                }
                continue;
            }

            $this->additionalHeaders[$key] = $header;
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
     * @throws Exception
     */
    public function parseMessage($message)
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
                                if (preg_match('(name="([^"]*)")ui', $attachment['type'], $m)) {
                                    $attachment['name'] = $m[1];
                                }
                            } elseif ($rowKey === 'Content-Disposition') {
                                $attachment['disposition'] = $rowHeader;
                            } elseif ($rowKey === 'Content-Transfer-Encoding') {
                                $attachment['encoding'] = $rowHeader;
                            }
                        }
                        $this->getAdapter()->getPhpMailer()->addStringAttachment(base64_decode($row['content']), $attachment['name'],
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
                            $this->setHtmlMessage($row['content']);
                        } else {
                            $this->setHtmlMessage($row['content']);
                            $this->setTextMessage(strip_tags($this->getHtmlMessage()));
                        }
                    }
                }
            }
        } else {
            if (stripos($this->getContentType(), 'text/plain') !== false) {
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
    public function parseAddresses($addresses)
    {
        if (!empty($addresses) && is_string($addresses)) {
            return array_map('trim', preg_split("([\n,;]+)ui", $addresses));
        }
        return [];
    }

    /**
     * @param $value
     * @param bool $isSender
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function parseAddress($value, $isSender=false)
    {
        $result = [
            'name' => '',
            'email' => $value,
        ];
        if (preg_match('((.*?)<([^>]+))ui', $value, $m)) {
            $result['name'] = trim($m[1]);
            $result['email'] = trim($m[2]);
        }
        if ($isSender || $this->getAdapter()->getRestrict()->checkAllowEmail($result['email'])) {
            return $result;
        }
        return null;
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
     * @return MailParser
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
     * @return MailParser
     */
    public function setTextMessage($textMessage)
    {
        $this->textMessage = $textMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     * @return MailParser
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function getSenderAddress()
    {
        $sender = $this->getAdapter()->getOptions()->getSender();
        if (!empty($sender)) {
            $address = $this->parseAddress($sender, true);
            $this->setSenderAddress($address);
        }

        return $this->senderAddress;
    }

    /**
     * @param mixed $sender
     * @return MailParser
     */
    public function setSenderAddress($sender)
    {
        $this->senderAddress = $sender;
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
     * @param $address
     */
    protected function addReplyToAddress($address)
    {
        $this->replyToAddresses[] = $address;
    }
    /**
     * @return array
     */
    public function getReplyToAddresses()
    {
        return $this->replyToAddresses;
    }

    /**
     * @return array
     */
    public function getCCAddresses()
    {
        return $this->CCAddresses;
    }

    /**
     * @return array
     */
    public function getBCCAddresses()
    {
        return $this->BCCAddresses;
    }

    /**
     * @param $address
     */
    protected function addCCAddress($address)
    {
        $this->CCAddresses[] = $address;
    }

    /**
     * @param $address
     */
    protected function addBCCAddress($address)
    {
        $this->BCCAddresses[] = $address;
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
     * @return MailParser
     */
    public function setIsHtml($isHtml)
    {
        $this->isHtml = $isHtml;
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
     * @return MailParser
     */
    public function setHtmlMessage($htmlMessage)
    {
        $this->htmlMessage = $htmlMessage;
        return $this;
    }
}