<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_Shop
 */
class GetresponseIntegration_Getresponse_Model_Resource_Shop extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('getresponse/shop', 'shop_id');
    }
}