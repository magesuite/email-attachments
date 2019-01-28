<?php

namespace MageSuite\EmailAttachments\Mail\Template;

use Zend\Mime\Mime;

class TransportBuilder
    extends \Magento\Framework\Mail\Template\TransportBuilder
{

    public function addAttachment($filePath)
    {
        if (!empty($filePath) && file_exists($filePath)) {
            $fileName = basename($filePath);
            $fileType = mime_content_type($filePath) ?: Mime::TYPE_OCTETSTREAM;
            $fileContent = file_get_contents($filePath);
            $this->message->setBodyAttachment($fileContent, $fileName, $fileType);
        }

        return $this;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();
        $this->message->setPartsToBody();
        return $this;
    }
}
