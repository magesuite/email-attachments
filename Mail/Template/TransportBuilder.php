<?php

namespace MageSuite\EmailAttachments\Mail\Template;

class TransportBuilder
    extends \Magento\Framework\Mail\Template\TransportBuilder
{

    public function addAttachmentByFilePath($filePath, $fileName = null)
    {
        if (!empty($filePath) && file_exists($filePath)) {
            $this->message
                ->createAttachment(
                    file_get_contents($filePath),
                    \Zend_Mime::TYPE_OCTETSTREAM,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $fileName ? basename($fileName) : basename($filePath)
                );
        }

        return $this;
    }
}
