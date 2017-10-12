<?php

class GetresponseIntegration_Getresponse_Model_Resource_ProductMap extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('getresponse/productMap', 'id');
    }
}