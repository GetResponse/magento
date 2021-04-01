<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization as LiveSynchronizationDTO;

class LiveSynchronization
{
    private $liveSynchronization;

    public function __construct(LiveSynchronizationDTO $liveSynchronization)
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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->liveSynchronization->getType();
    }
}
