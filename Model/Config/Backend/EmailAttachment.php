<?php

namespace MageSuite\EmailAttachments\Model\Config\Backend;

class EmailAttachment extends \Magento\Config\Model\Config\Backend\File
{
    protected function _getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'odt'];
    }
}
