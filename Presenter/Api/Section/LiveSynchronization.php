<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization as LiveSynchronizationDTO;

class LiveSynchronization
{
    /** @var LiveSynchronizationDTO */
    private $liveSynchronization;

    /**
     * @param LiveSynchronizationDTO $liveSynchronization
     */
    public function __construct(LiveSynchronizationDTO $liveSynchronization)
    {
        $this->liveSynchronization = $liveSynchronization;
    }

    /**
     * Get is active.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->liveSynchronization->isActive();
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->liveSynchronization->getCallbackUrl();
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->liveSynchronization->getType();
    }
}
