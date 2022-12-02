<?php

namespace MageSuite\EmailAttachments\Helper;

class Configuration
{
    protected $templateIdentifiers = [
        'sales_email/order/guest_template',
        'sales_email/order/template'
    ];

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function getAttachment(string $field, $storeId = null): string
    {
        $path = 'sales_email/attachments/attachment_' . $field;

        return (string)$this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getTemplateIdentifiers($storeId = null): array
    {
        $templateIdentifiers = [];

        foreach ($this->templateIdentifiers as $templateIdentifier) {
            $templateIdentifiers[] = $this->scopeConfig->getValue($templateIdentifier, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }

        return $templateIdentifiers;
    }
}
