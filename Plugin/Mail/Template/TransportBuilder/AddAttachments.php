<?php

namespace MageSuite\EmailAttachments\Plugin\Mail\Template\TransportBuilder;


class AddAttachments
{
    const ATTACHMENT_PATH = '/sales/store/order/attachments/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    const ATTACHMENTS = [
        'sales_email/attachments/attachment_first',
        'sales_email/attachments/attachment_second',
        'sales_email/attachments/attachment_third'
    ];

    const TEMPLATE_IDENTIFIERS = [
        'sales_email_order_guest_template',
        'sales_email_order_template'
    ];

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
        if (in_array($templateIdentifier, self::TEMPLATE_IDENTIFIERS)) {
            foreach(self::ATTACHMENTS as $attachmentsPath) {
                $attachment = $this->getAttachment($attachmentsPath);
                $subject->addAttachmentByFilePath($attachment);
            }
        }

        return [$templateIdentifier];
    }

    private function getAttachment($path)
    {
        $path = $this->scopeConfig->getValue(
            $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->mediaDirectory->getAbsolutePath(self::ATTACHMENT_PATH . $path);
    }

}