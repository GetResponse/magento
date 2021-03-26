<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;

class WebFormPresenter
{
    private $webForm;

    public function __construct(WebForm $webForm)
    {
        $this->webForm = $webForm;
    }


    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->webForm->isEnabled();
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->webForm->getWebFormId();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->webForm->getUrl();
    }

    /**
     * @return string
     */
    public function getBlock(): string
    {
        return $this->webForm->getSidebar();
    }
}
