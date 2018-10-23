<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Shop
 */
class GetresponseIntegration_Getresponse_Domain_Shop
{
    /** @var string */
    private $grShopId;

    /** @var string */
    private $grListId;

    /** @var bool */
    private $isEnabled;

    /** @var bool */
    private $isScheduleOptimizationEnabled;

    /**
     * @param string $grShopId
     * @param string $grListId
     * @param bool $isEnabled
     * @param bool $isScheduleOptimizationEnabled
     */
    public function __construct($grShopId, $grListId, $isEnabled, $isScheduleOptimizationEnabled)
    {
        $this->grShopId = $grShopId;
        $this->grListId = $grListId;
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
            'grListId' => $this->grListId,
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
