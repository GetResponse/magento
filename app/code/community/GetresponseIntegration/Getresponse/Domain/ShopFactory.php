<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;

/**
 * Class GetresponseIntegration_Getresponse_Domain_ShopFactory
 */
class GetresponseIntegration_Getresponse_Domain_ShopFactory
{
    /**
     * @param array $data
     * @return GetresponseIntegration_Getresponse_Domain_Shop
     */
    public static function createFromArray($data)
    {
        return new Shop(
            $data['grShopId'],
            $data['isEnabled'],
            $data['isScheduleOptimizationEnabled']
        );
    }
}
