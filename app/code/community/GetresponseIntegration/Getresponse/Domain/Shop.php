<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Shop
 */
class GetresponseIntegration_Getresponse_Domain_Shop
{
    private $grShopId;
    private $isEnabled;
    private $isScheduleOptimizationEnabled;

    /**
     * GetresponseIntegration_Getresponse_Domain_Shop constructor.
     *
     * @param $grShopId
     * @param $isEnabled
     * @param $isScheduleOptimizationEnabled
     */
    public function __construct($grShopId, $isEnabled, $isScheduleOptimizationEnabled)
    {
        $this->grShopId = $grShopId;
        $this->isEnabled = $isEnabled;
        $this->isScheduleOptimizationEnabled = $isScheduleOptimizationEnabled;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'grShopId' => $this->grShopId,
            'isEnabled' => $this->isEnabled,
            'isScheduleOptimizationEnabled' => $this->isScheduleOptimizationEnabled
        );
    }
}