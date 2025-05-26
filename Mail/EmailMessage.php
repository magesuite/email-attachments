<?php

declare(strict_types=1);

namespace MageSuite\EmailAttachments\Mail;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Psr\Log\LoggerInterface;
use \Magento\Framework\Mail\MimeMessageInterfaceFactory;
use \Magento\Framework\Mail\AddressFactory;
use \Magento\Framework\Mail\Address;
use \Magento\Framework\Mail\MimeMessageInterface;

class EmailMessage extends \Magento\Framework\Mail\Message implements \Magento\Framework\Mail\EmailMessageInterface
{
    private MimeMessageInterfaceFactory $mimeMessageFactory;
    private AddressFactory $addressFactory;
    private ?LoggerInterface $logger;
    protected Mailer $mailer;

    public function __construct(
        MimeMessageInterface $body,
        array $to,
        MimeMessageInterfaceFactory $mimeMessageFactory,
        AddressFactory $addressFactory,
        protected \Magento\Framework\Mail\MimePartFactory $mimePartFactory,
        ?array $from = null,
        ?array $cc = null,
        ?array $bcc = null,
        ?array $replyTo = null,
        ?Address $sender = null,
        ?string $subject = '',
        ?string $encoding = 'utf-8',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($encoding);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->addressFactory = $addressFactory;
        $this->symfonyMessage = $body->getMimeMessage();
        $this->setBody($this->symfonyMessage);

        if (!empty($subject)) {
            $this->symfonyMessage->getHeaders()->addTextHeader('Subject', $subject);
        }

        $this->setSender($sender);
        $this->setRecipients($to, 'To');
        $this->setRecipients($replyTo, 'Reply-To');
        $this->setRecipients($from, 'From');
        $this->setRecipients($cc, 'Cc');
        $this->setRecipients($bcc, 'Bcc');
    }

    public function getSymfonyMessage(): SymfonyMessage
    {
        return $this->symfonyMessage;
    }

    private function setSender(?Address $sender): void
    {
        if ($sender) {
            $this->symfonyMessage->getHeaders()->addMailboxHeader(
                'Sender',
                new SymfonyAddress($this->sanitiseEmail($sender->getEmail()), $sender->getName())
            );
        }
    }

    private function setRecipients(?array $addresses, string $method): void
    {
        if ($method === 'to' && (empty($addresses) || count($addresses) < 1)) {
            throw new InvalidArgumentException('Email message must have at least one addressee');
        }

        if (!$addresses) {
            return;
        }

        $recipients = [];
        foreach ($addresses as $address) {
            try {
                if ($address instanceof Address) {
                    $recipients[] = new SymfonyAddress(
                        $this->sanitiseEmail($address->getEmail()),
                        $address->getName() ?? ''
                    );
                } else {
                    $recipients[] = new SymfonyAddress(
                        $this->sanitiseEmail($address['email']),
                        $address['name'] ?? ''
                    );
                }
            } catch (\Exception $e) {
                $this->logger->warning(
                    'Could not add an invalid email address to the mailing queue',
                    ['exception' => $e]
                );
                continue;
            }

        }

        $this->symfonyMessage->getHeaders()->addMailboxListHeader($method, $recipients);
    }

    public function getEncoding(): string
    {
        return $this->symfonyMessage->getHeaders()->getHeaderBody('Content-Transfer-Encoding');
    }

    public function getHeaders(): array
    {
        return $this->symfonyMessage->getHeaders()->toArray();
    }

    public function getFrom(): ?array
    {
        return $this->getAddresses('From');
    }

    public function getTo(): array
    {
        return $this->getAddresses('To') ?? [];
    }

    public function getCc(): ?array
    {
        return $this->getAddresses('Cc');
    }

    public function getBcc(): ?array
    {
        return $this->getAddresses('Bcc');
    }

    public function getReplyTo(): ?array
    {
        return $this->getAddresses('Reply-To');
    }

    private function getAddresses(string $headerName): ?array
    {
        $header = $this->symfonyMessage->getHeaders()->get($headerName);
        if ($header) {
            return $this->convertAddressListToAddressArray($header->getAddresses());
        }

        return null;
    }

    public function getSender(): ?Address
    {
        $senderHeader = $this->symfonyMessage->getHeaders()->get('Sender');
        if (!$senderHeader) {
            return null;
        }

        $senderAddress = $senderHeader->getAddress();
        if (!$senderAddress) {
            return null;
        }

        return $this->addressFactory->create([
            'email' => $senderAddress->getAddress(),
            'name' => $senderAddress->getName()
        ]);
    }

    public function getMessageBody(): MimeMessageInterface
    {
        $parts = [];
        if ($this->symfonyMessage->getBody() instanceof TextPart) {
            $parts[] = $this->symfonyMessage->getBody();
        }

        return $this->mimeMessageFactory->create(['parts' => $parts]);
    }

    public function getBodyText(): string
    {
        return $this->symfonyMessage->getTextBody() ?? '';
    }

    public function getBodyHtml(): string
    {
        return $this->symfonyMessage->getHtmlBody() ?? '';
    }

    public function toString(): string
    {
        return $this->symfonyMessage->toString();
    }

    private function convertAddressListToAddressArray(array $addressList): array
    {
        return array_map(function ($address) {
            return $this->addressFactory->create([
                'email' => $this->sanitiseEmail($address->getAddress()),
                'name' => $address->getName()
            ]);
        }, $addressList);
    }

    private function sanitiseEmail(?string $email): ?string
    {
        if (!empty($email) && str_starts_with($email, '=?')) {
            $decodedValue = iconv_mime_decode($email, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if (str_contains($decodedValue, ' ')) {
                throw new InvalidArgumentException('Invalid email format');
            }
        }

        return $email;
    }

    /***************************************************************************
    *                        MODIFICATION START                                *
    ****************************************************************************/
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
