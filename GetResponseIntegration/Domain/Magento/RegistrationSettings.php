<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class RegistrationSettings
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class RegistrationSettings
{
    /** @var int */
    private $status;

    /** @var int */
    private $customFieldsStatus;

    /** @var string */
    private $campaignId;

    /** @var int */
    private $cycleDay;

    /**
     * @param int $status
     * @param int $customFieldsStatus
     * @param string $campaignId
     * @param int $cycleDay
     */
    public function __construct($status, $customFieldsStatus, $campaignId, $cycleDay)
    {
        $this->status = $status;
        $this->customFieldsStatus = $customFieldsStatus;
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
     * @return bool
     */
    public function isCustomFieldsModified()
    {
        return 1 === (int) $this->customFieldsStatus;
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
            'customFieldsStatus' => $this->customFieldsStatus,
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
