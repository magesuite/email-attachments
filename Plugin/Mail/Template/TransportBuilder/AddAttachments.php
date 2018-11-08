<?php

namespace MageSuite\EmailAttachments\Plugin\Mail\Template\TransportBuilder;


class AddAttachments
{
    const ATTACHMENT_PATH = '/sales/store/order/attachments/';

    const TEMPLATE_IDENTIFIERS = [
        'sales_email/order/guest_template',
        'sales_email/order/template'
    ];

    const ATTACHMENTS = [
        'sales_email/attachments/attachment_first',
        'sales_email/attachments/attachment_second',
        'sales_email/attachments/attachment_third'
    ];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    public function beforeSetTemplateIdentifier(\MageSuite\EmailAttachments\Mail\Template\TransportBuilder $subject, $templateIdentifier)
    {
        if (in_array($templateIdentifier, $this->getTemplateIdentifiers())) {
            foreach(self::ATTACHMENTS as $attachment) {
                $attachmentPath = $this->getAttachmentPath($attachment);
                $subject->addAttachmentByFilePath($attachmentPath);
            }
        }

        return [$templateIdentifier];
    }

    private function getAttachmentPath($attachment)
    {
        $path = $this->scopeConfig->getValue(
            $attachment, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $path ? $this->mediaDirectory->getAbsolutePath(self::ATTACHMENT_PATH . $path) : '';
    }

    private function getTemplateIdentifiers() {
        $templateIdentifiers = [];
        foreach (self::TEMPLATE_IDENTIFIERS as $templateIdentifier) {
            $templateIdentifiers[] = $this->scopeConfig->getValue(
                $templateIdentifier, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $templateIdentifiers;
    }
}
