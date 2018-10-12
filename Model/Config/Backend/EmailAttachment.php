<?php

namespace MageSuite\EmailAttachments\Model\Config\Backend;

class EmailAttachment extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['pdf', 'doc', 'docx', 'odt'];
    }
}