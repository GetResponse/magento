<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class NewsletterSettings
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class NewsletterSettings
{
    /** @var int */
    private $status;

    /** @var string */
    private $campaignId;

    /** @var int */
    private $cycleDay;

    /** @var string */
    private $autoresponderId;

    /**
     * @param int $status
     * @param string $campaignId
     * @param int $cycleDay
     * @param string $autoresponderId
     */
    public function __construct($status, $campaignId, $cycleDay, $autoresponderId)
    {
        $this->status = $status;
        $this->campaignId = $campaignId;
        $this->cycleDay = $cycleDay;
        $this->autoresponderId = $autoresponderId;
    }

    /**
     * @return string
     */
    public function getAutoresponderId()
    {
        return $this->autoresponderId;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return 1 === (int)$this->status;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'status' => $this->status,
            'campaignId' => $this->campaignId,
            'cycleDay' => $this->cycleDay,
            'autoresponderId' => $this->autoresponderId
        ];
    }

    /**
     * @return int
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }
}
