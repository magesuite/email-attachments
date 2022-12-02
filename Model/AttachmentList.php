<?php

namespace MageSuite\EmailAttachments\Model;

class AttachmentList implements AttachmentListInterface
{
    protected array $attachments = [];

    public function __construct(array $attachments = [])
    {
        $this->attachments = $attachments;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
