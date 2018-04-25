<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Shop
 */
class GetresponseIntegration_Getresponse_Domain_Shop
{
    /** @var string */
    private $grShopId;

    /** @var bool */
    private $isEnabled;

    /** @var bool */
    private $isScheduleOptimizationEnabled;

    /**
     * @param string $grShopId
     * @param bool $isEnabled
     * @param bool $isScheduleOptimizationEnabled
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

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return 1 === $this->isEnabled;
    }
}
