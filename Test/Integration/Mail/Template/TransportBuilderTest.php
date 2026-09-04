<?php

declare(strict_types=1);

namespace MageSuite\EmailAttachments\Test\Integration\Mail\Template;

class TransportBuilderTest extends \PHPUnit\Framework\TestCase
{
    protected const ATTACHMENT_FILE_NAME = 'test_attachment.txt';
    protected const ATTACHMENT_CONTENT = "Attachment content\n";

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\EmailAttachments\Mail\Template\TransportBuilder $transportBuilder;
    protected ?\Magento\Email\Model\Template $template;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->transportBuilder = $this->objectManager->create(\MageSuite\EmailAttachments\Mail\Template\TransportBuilder::class);
        $this->template = $this->objectManager->get(\Magento\Email\Model\Template::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testAddAttachmentAddsAttachmentToMessage(): void
    {
        $fileName = self::ATTACHMENT_FILE_NAME;
        $filePath = $this->getAttachmentFixturePath();
        $this->prepareBuilder();

        $this->transportBuilder->addAttachment($filePath);

        $message = $this->transportBuilder->getTransport()->getMessage();
        $messageString = $message->toString();

        $this->assertStringContainsString('Content-Disposition: attachment', $messageString);
        $this->assertStringContainsString(base64_encode(self::ATTACHMENT_CONTENT), $messageString);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testAddAttachmentSkipsMissingFile(): void
    {
        $this->prepareBuilder();

        $this->transportBuilder->addAttachment('/not-existing-file.txt');

        $message = $this->transportBuilder->getTransport()->getMessage();
        $messageString = $message->toString();

        $this->assertStringNotContainsString('Content-Disposition: attachment', $messageString);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Email/Model/_files/email_template.php
     */
    public function testAddAttachmentFromContentAddsAttachmentToMessage(): void
    {
        $this->prepareBuilder();

        $this->transportBuilder->addAttachmentFromContent(self::ATTACHMENT_CONTENT, self::ATTACHMENT_FILE_NAME, 'text/plain');

        $message = $this->transportBuilder->getTransport()->getMessage();
        $messageString = $message->toString();

        $this->assertStringContainsString('Content-Disposition: attachment', $messageString);
        $this->assertStringContainsString(base64_encode(self::ATTACHMENT_CONTENT), $messageString);
    }

    protected function prepareBuilder(): void
    {
        $template = $this->template->load('email_exception_fixture', 'template_code');
        $templateId = $template->getId();

        $this->transportBuilder->setTemplateModel(\Magento\Email\Model\BackendTemplate::class);
        $this->transportBuilder->setTemplateIdentifier($templateId);
        $this->transportBuilder->setTemplateVars(['reason' => 'Reason', 'customer' => 'Customer']);
        $this->transportBuilder->setTemplateOptions(['area' => 'frontend', 'store' => 1]);
        $this->transportBuilder->setFromByScope('general', 1);
        $this->transportBuilder->addTo('john.doe@example.com');
    }

    protected function getAttachmentFixturePath(): string
    {
        return sprintf('%s/../../_files/%s', __DIR__, self::ATTACHMENT_FILE_NAME);
    }

}
