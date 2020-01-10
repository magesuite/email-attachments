<?php

namespace MageSuite\EmailAttachments\Mail;

class Message implements \Magento\Framework\Mail\MailMessageInterface
{
    /**
     * @var \Zend\Mime\PartFactory
     */
    protected $partFactory;

    /**
     * @var \Zend\Mime\MessageFactory
     */
    protected $mimeMessageFactory;

    /**
     * @var \Zend\Mail\Message
     */
    private $zendMessage;

    /**
     * @var \Zend\Mime\Part[]
     */
    protected $parts = [];

    /**
     * Message type
     *
     * @var string
     */
    private $messageType = self::TYPE_TEXT;

    /**
     * Message constructor.
     *
     * @param \Zend\Mime\PartFactory $partFactory
     * @param \Zend\Mime\MessageFactory $mimeMessageFactory
     * @param string $charset
     */
    public function __construct(
        \Zend\Mime\PartFactory $partFactory,
        \Zend\Mime\MessageFactory $mimeMessageFactory,
        $charset = 'utf-8'
    )
    {
        $this->partFactory = $partFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->zendMessage = \Zend\Mail\MessageFactory::getInstance();
        $this->zendMessage->setEncoding($charset);
    }

    /**
     * @inheritdoc
     *
     * @deprecated 101.0.8
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @deprecated 101.0.8
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody($body)
    {
        if (is_string($body) && $this->messageType === \Magento\Framework\Mail\MailMessageInterface::TYPE_HTML) {
            $body = self::createHtmlMimeFromString($body);

            foreach ($body->getParts() as $part) {
                $this->parts[] = $part;
            }
        }

        if(!$this->hasAttachment()){
            $this->parts = [];
            $this->zendMessage->setBody($body);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * @inheritdoc
     *
     * @deprecated 102.0.1 This function is missing the from name. The
     * setFromAddress() function sets both from address and from name.
     * @see setFromAddress()
     */
    public function setFrom($fromAddress)
    {
        $this->setFromAddress($fromAddress, null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * Create HTML mime message from the string.
     *
     * @param string $htmlBody
     * @return \Zend\Mime\Message
     */
    private function createHtmlMimeFromString($htmlBody)
    {
        $htmlPart = new \Zend\Mime\Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(\Zend\Mime\Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }

    /**
     * @inheritdoc
     */
    public function setBodyHtml($html)
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    /**
     * @inheritdoc
     */
    public function setBodyText($text)
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }

    /**
     * Add the attachment mime part to the message.
     *
     * @param string $content
     * @param string $fileName
     * @param string $fileType
     * @return $this
     */
    public function setBodyAttachment($content, $fileName, $fileType)
    {
        $attachmentPart = $this->partFactory->create();
        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setEncoding(\Zend\Mime\Mime::ENCODING_BASE64)
            ->setDisposition(\Zend\Mime\Mime::DISPOSITION_ATTACHMENT);
        $this->parts[] = $attachmentPart;
        return $this;
    }

    /**
     * Set parts to Zend message body.
     *
     * @return $this
     */
    public function setPartsToBody()
    {
        if ($this->hasAttachment()) {
            $mimeMessage = $this->mimeMessageFactory->create();
            $mimeMessage->setParts($this->parts);
            $this->zendMessage->setBody($mimeMessage);
        }
        return $this;
    }

    private function hasAttachment()
    {
        foreach ($this->parts as $part) {
            if($part->getDisposition() == \Zend\Mime\Mime::DISPOSITION_ATTACHMENT && !empty($part->getFileName()) && $part->getEncoding() == \Zend\Mime\Mime::ENCODING_BASE64){
                return true;
            }
        }

        return false;
    }
}
