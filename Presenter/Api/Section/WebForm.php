<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\WebForm as WebFormDTO;

class WebForm
{
    /** @var WebFormDTO */
    private $webForm;

    /** @param WebFormDTO $webForm */
    public function __construct(WebFormDTO $webForm)
    {
        $this->webForm = $webForm;
    }

    /**
     * Get is active.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->webForm->isEnabled();
    }

    /**
     * Get form id.
     *
     * @return string
     */
    public function getFormId(): string
    {
        return $this->webForm->getWebFormId();
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->webForm->getUrl();
    }

    /**
     * Get block.
     *
     * @return string
     */
    public function getBlock(): string
    {
        return $this->webForm->getSidebar();
    }
}
