<?php

declare(strict_types=1);

namespace MageSuite\EmailAttachments\Mail;

class EmailMessage extends \Magento\Framework\Mail\EmailMessage implements \Magento\Framework\Mail\EmailMessageInterface
{
    protected array $attachments = [];

    public function addAttachments(array $attachments): self
    {
        $this->attachments = $attachments;
        $parts = [
            $this->symfonyMessage->getBody(),
            ...array_map(
                fn($attachment) => $attachment->getMimePart(),
                $this->attachments
            )
        ];
        $body = new \Symfony\Component\Mime\Part\Multipart\MixedPart(...$parts);
        $this->symfonyMessage->setBody($body);

        return $this;
    }
}
