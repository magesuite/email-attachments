<?php

namespace MageSuite\EmailAttachments\Mail;

class Message implements \Magento\Framework\Mail\MailMessageInterface
{
    protected \Laminas\Mime\PartFactory $partFactory;

    protected \Laminas\Mime\MessageFactory $mimeMessageFactory;

    protected \Laminas\Mail\Message $zendMessage;

    protected array $parts = [];

    public function __construct(
        \Laminas\Mime\PartFactory $partFactory,
        \Laminas\Mime\MessageFactory $mimeMessageFactory,
        $charset = 'utf-8'
    ) {
        $this->partFactory = $partFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->zendMessage = \Laminas\Mail\MessageFactory::getInstance();
        $this->zendMessage->setEncoding($charset);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType($type)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody($body)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($fromAddress)
    {
        $this->zendMessage->setFrom($fromAddress);
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
     * {@inheritdoc}
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * Add the text mime part to the message.
     *
     * @param string $content
     * @return $this
     */
    public function setBodyHtml($content)
    {
        $htmlPart = $this->partFactory->create();
        $htmlPart->setContent($content)
            ->setType(\Laminas\Mime\Mime::TYPE_HTML)
            ->setCharset($this->zendMessage->getEncoding());
        $this->parts[] = $htmlPart;
        return $this;
    }

    /**
     * Add the HTML mime part to the message.
     *
     * @param string $content
     * @return $this
     */
    public function setBodyText($content)
    {
        $textPart = $this->partFactory->create();
        $textPart->setContent($content)
            ->setType(\Laminas\Mime\Mime::TYPE_TEXT)
            ->setCharset($this->zendMessage->getEncoding());
        $this->parts[] = $textPart;
        return $this;
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
            ->setEncoding(\Laminas\Mime\Mime::ENCODING_BASE64)
            ->setDisposition(\Laminas\Mime\Mime::DISPOSITION_ATTACHMENT);
        $this->parts[] = $attachmentPart;
        return $this;
    }

    public function setPartsToBody()
    {
        $mimeMessage = $this->mimeMessageFactory->create();
        $mimeMessage->setParts($this->parts);
        $this->zendMessage->setBody($mimeMessage);
        return $this;
    }
}
