<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class NewsletterSettings
{
    private $status;
    private $campaignId;
    private $cycleDay;
    private $autoresponderId;

    public function __construct(
        int $status,
        string $campaignId,
        $cycleDay,
        string $autoresponderId
    ) {
        $this->status = $status;
        $this->campaignId = $campaignId;
        $this->cycleDay = $cycleDay;
        $this->autoresponderId = $autoresponderId;
    }

    public function getAutoresponderId(): string
    {
        return $this->autoresponderId;
    }

    public function isEnabled(): bool
    {
        return 1 === $this->status;
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'campaignId' => $this->campaignId,
            'cycleDay' => $this->cycleDay,
            'autoresponderId' => $this->autoresponderId
        ];
    }

    public function getCycleDay()
    {
        return $this->cycleDay;
    }
}
