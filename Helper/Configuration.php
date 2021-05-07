<?php

namespace MageSuite\EmailAttachments\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $templateIdentifiers = [
        'sales_email/order/guest_template',
        'sales_email/order/template'
    ];

    public function getAttachment(string $field, $storeId = null): string
    {
        $path = 'sales_email/attachments/attachment_' . $field;

        return (string)$this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTemplateIdentifiers($storeId = null): array
    {
        $templateIdentifiers = [];

        foreach ($this->templateIdentifiers as $templateIdentifier) {
            $templateIdentifiers[] = $this->scopeConfig->getValue(
                $templateIdentifier,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $templateIdentifiers;
    }
}
