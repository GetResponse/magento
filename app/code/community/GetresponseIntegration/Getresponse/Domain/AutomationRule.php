<?php
/**
 * Created by PhpStorm.
 * User: mjaniszewski
 * Date: 12/12/2017
 * Time: 10:06
 */

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
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return mixed
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }

    public function toArray()
    {
        return [
            'categoryId'    => $this->categoryId,
            'campaignId'    => $this->campaignId,
            'action'        => $this->action,
            'cycleDay'      => $this->cycleDay,
            'active'        => $this->active
        ];
    }
}