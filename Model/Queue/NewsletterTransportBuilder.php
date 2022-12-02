<?php

namespace MageSuite\EmailAttachments\Model\Queue;

class NewsletterTransportBuilder extends \Magento\Newsletter\Model\Queue\TransportBuilder
{
    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {
        /** @var \Magento\Email\Model\AbstractTemplate $template */
        $template = $this->getTemplate()->setData($this->templateData);
        $this->setTemplateFilter($template);

        $this->message->setBodyHtml(
            $template->getProcessedTemplate($this->templateVars)
        )->setSubject(
            $template->getSubject()
        );
        
        $this->message->setPartsToBody();

        return $this;
    }
}
