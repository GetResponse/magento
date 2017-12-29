<?php
use GetresponseIntegration_Getresponse_Domain_Shop as Shop;

class GetresponseIntegration_Getresponse_Domain_ShopFactory
{
    public static function createFromArray($data)
    {
        return new Shop(
            $data['grShopId'],
            $data['isEnabled']
        );
    }
}