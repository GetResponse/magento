<?php

class GetresponseIntegration_Getresponse_Model_ProductMap extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('getresponse/productMap');
    }
}