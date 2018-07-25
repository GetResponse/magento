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

    /**
     * @param int $status
     * @param string $campaignId
     * @param int $cycleDay
     */
    public function __construct($status, $campaignId, $cycleDay)
    {
        $this->status = $status;
        $this->campaignId = $campaignId;
        $this->cycleDay = $cycleDay;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (1 === (int)$this->status) ? true : false;
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
            'cycleDay' => $this->cycleDay
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
