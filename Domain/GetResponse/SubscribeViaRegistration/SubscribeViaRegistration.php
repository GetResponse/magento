<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration;

class SubscribeViaRegistration
{
    private $status;
    private $customFieldsStatus;
    private $campaignId;
    private $cycleDay;
    private $autoresponderId;

    public function __construct(
        int $status,
        int $customFieldsStatus,
        string $campaignId,
        $cycleDay,
        string $autoresponderId
    ) {
        $this->status = $status;
        $this->customFieldsStatus = $customFieldsStatus;
        $this->campaignId = $campaignId;
        $this->cycleDay = $cycleDay;
        $this->autoresponderId = $autoresponderId;
    }

    public function isEnabled(): bool
    {
        return 1 === $this->status;
    }

    public function getAutoresponderId(): string
    {
        return $this->autoresponderId;
    }

    public function isUpdateCustomFieldsEnalbed(): bool
    {
        return 1 === $this->customFieldsStatus;
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'customFieldsStatus' => $this->customFieldsStatus,
            'campaignId' => $this->campaignId,
            'cycleDay' => $this->cycleDay,
            'autoresponderId' => $this->autoresponderId
        ];
    }

    public function getCycleDay()
    {
        if ($this->cycleDay !== '') {
            return $this->cycleDay;
        }

        return null;
    }
}
