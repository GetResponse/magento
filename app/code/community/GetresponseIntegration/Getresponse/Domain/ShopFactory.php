<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;

class GetresponseIntegration_Getresponse_Domain_ShopFactory
{
    /**
     * @param $data
     * @return GetresponseIntegration_Getresponse_Domain_Shop
     */
    public static function createFromArray($data)
    {
        return new Shop(
            $data['grShopId'],
            $data['isEnabled']
        );
    }
}