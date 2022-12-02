<?php

namespace MageSuite\EmailAttachments\Plugin\Mail\Template\TransportBuilder;

class AddAttachments
{
    const ATTACHMENT_PATH = '/sales/store/order/attachments/';

    protected \MageSuite\EmailAttachments\Model\AttachmentList $attachmentList;

    protected \MageSuite\EmailAttachments\Helper\Configuration $configuration;

    protected \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory;

    public function __construct(
        \MageSuite\EmailAttachments\Model\AttachmentList $attachmentList,
        \MageSuite\EmailAttachments\Helper\Configuration $configuration,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->attachmentList = $attachmentList;
        $this->configuration = $configuration;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    public function afterSetTemplateOptions(\MageSuite\EmailAttachments\Mail\Template\TransportBuilder $subject, $result, $templateOptions)
    {
        $storeId = $templateOptions['store'] ?? null;

        if (!$subject->getTemplateIdentifier() || !in_array($subject->getTemplateIdentifier(), $this->configuration->getTemplateIdentifiers($storeId))) {
            return $result;
        }

        foreach ($this->attachmentList->getAttachments() as $attachment) {
            $attachmentFileName = $this->configuration->getAttachment($attachment, $storeId);

            if (empty($attachmentFileName)) {
                continue;
            }

            $attachmentPath = $this->getAttachmentPath($attachmentFileName);
            $subject->addAttachment($attachmentPath);
        }

        return $result;
    }

    protected function getAttachmentPath(string $fileName): string
    {
        return $this->mediaDirectory->getAbsolutePath(self::ATTACHMENT_PATH . $fileName);
    }
}
