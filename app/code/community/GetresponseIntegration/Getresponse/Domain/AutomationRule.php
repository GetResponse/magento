<?php

class GetresponseIntegration_Getresponse_Domain_AutomationRule
{
    private $id;
    private $categoryId;
    private $campaignId;
    private $action;
    private $cycleDay;
    private $active;

    /**
     * GetresponseIntegration_Getresponse_Domain_AutomationRule constructor.
     * @param $categoryId
     * @param $campaignId
     * @param $action
     * @param $cycleDay
     * @param $active
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
        return [
            'id' => $this->id,
            'categoryId' => $this->categoryId,
            'campaignId' => $this->campaignId,
            'action' => $this->action,
            'cycleDay' => $this->cycleDay,
            'active' => $this->active
        ];
    }
}