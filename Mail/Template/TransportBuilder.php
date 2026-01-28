<?php
// phpcs:disable Standard.Classes.RequireFullPath
// phpcs:disable Standard.Classes.PrivateConstructParameters
// phpcs:disable Standard.Plugins.UnderscorePrefix
// phpcs:disable Magento2.Functions.DiscouragedFunction

declare(strict_types=1);

namespace MageSuite\EmailAttachments\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Mail\Template\{FactoryInterface, SenderResolverInterface};

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    private $messageData = [];
    private $emailMessageInterfaceFactory;
    private $mimeMessageInterfaceFactory;
    private $mimePartInterfaceFactory;
    private $addressConverter;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        ?MessageInterfaceFactory $messageFactory = null,
        ?EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        ?MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        ?MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        ?AddressConverter $addressConverter = null
    ) {
        $this->templateFactory = $templateFactory;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
            ->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
            ->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
            ->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $addressConverter ?: $this->objectManager
            ->get(AddressConverter::class);
    }

    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }

    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }

    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;

        return $this;
    }

    public function setTemplateModel($templateModel)
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;

        return $this;
    }

    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;

        return $this;
    }

    public function getTransport()
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }

    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }

    /***************************************************************************
                                MODIFICATION START
     **************************************************************************/

    protected array $attachments = [];

    public function addAttachment($filePath)
    {
        if (empty($filePath) || !file_exists($filePath)) {
            return $this;
        }

        $fileName = basename($filePath);
        $fileType = mime_content_type($filePath) ?: \Magento\Framework\HTTP\Mime::TYPE_OCTETSTREAM;
        $fileContent = file_get_contents($filePath);

        $attachmentPart = $this->mimePartInterfaceFactory->create([
            'content' => $fileContent,
            'type' => $fileType,
            'fileName' => $fileName,
            'disposition' => \Magento\Framework\HTTP\Mime::DISPOSITION_ATTACHMENT,
            'encoding' => \Magento\Framework\HTTP\Mime::ENCODING_BASE64
        ]);

        $this->attachments[] = $attachmentPart;

        return $this;
    }

    public function addAttachmentFromContent(?string $content, ?string $fileName, ?string $fileType): self
    {
        if (empty($content) || empty($fileName) || empty($fileType)) {
            return $this;
        }

        $attachmentPart = $this->mimePartInterfaceFactory->create([
            'content' => $content,
            'type' => $fileType,
            'fileName' => $fileName,
            'disposition' => \Magento\Framework\HTTP\Mime::DISPOSITION_ATTACHMENT,
            'encoding' => \Magento\Framework\HTTP\Mime::ENCODING_BASE64
        ]);

        $this->attachments[] = $attachmentPart;

        return $this;
    }

    public function getTemplateIdentifier()
    {
        return $this->templateIdentifier;
    }

    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $partType = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $partType = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }

        /** @var \Magento\Framework\Mail\MimePartInterface $mimePart */
        $mimePart = $this->mimePartInterfaceFactory->create(
            [
                'content' => $content,
                'type' => $partType
            ]
        );
        $this->messageData['encoding'] = $mimePart->getCharset();
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $this->messageData['subject'] = html_entity_decode(
            (string)$template->getSubject(),
            ENT_QUOTES
        );

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        if (!empty($this->attachments)) {
            $this->message->addAttachments($this->attachments);
        }

        return $this;
    }

    protected function reset()
    {
        parent::reset();
        $this->messageData = [];
        $this->attachments = [];

        return $this;
    }
}
