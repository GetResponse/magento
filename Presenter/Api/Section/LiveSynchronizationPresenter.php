<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;

class LiveSynchronizationPresenter
{
    private $liveSynchronization;

    public function __construct(LiveSynchronization $liveSynchronization)
    {
        $this->liveSynchronization = $liveSynchronization;
    }


    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->liveSynchronization->isActive();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->liveSynchronization->getCallbackUrl();
    }
}
