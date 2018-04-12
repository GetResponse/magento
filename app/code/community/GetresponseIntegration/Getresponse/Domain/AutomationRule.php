<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_AutomationRule
 */
class GetresponseIntegration_Getresponse_Domain_AutomationRule
{
    /** @var string */
    private $id;

    /** @var string */
    private $categoryId;

    /** @var string */
    private $campaignId;

    /** @var string */
    private $action;

    /** @var string */
    private $cycleDay;

    /** @var bool */
    private $active;

    /**
     * @param string $id
     * @param string $categoryId
     * @param string $campaignId
     * @param string $action
     * @param string $cycleDay
     * @param bool   $active
     */
    public function __construct(
        $id,
        $categoryId,
        $campaignId,
        $action,
        $cycleDay,
        $active
    ) {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->campaignId = $campaignId;
        $this->action = $action;
        $this->cycleDay = $cycleDay;
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'categoryId' => $this->categoryId,
            'campaignId' => $this->campaignId,
            'action' => $this->action,
            'cycleDay' => $this->cycleDay,
            'active' => $this->active
        );
    }
}
