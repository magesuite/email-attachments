<?php

namespace MageSuite\EmailAttachments\Plugin;


class TransportBuilderAddAttachments
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
        if ($templateIdentifier == 'sales_email_order_template') {
            $attachmentFirst = $this->getAttachment('sales_email/attachments/attachment_first');
            $subject->addAttachmentByFilePath($attachmentFirst);

            $attachmentSecond = $this->getAttachment('sales_email/attachments/attachment_second');
            $subject->addAttachmentByFilePath($attachmentSecond);

            $attachmentThird = $this->getAttachment('sales_email/attachments/attachment_third');
            $subject->addAttachmentByFilePath($attachmentThird);
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