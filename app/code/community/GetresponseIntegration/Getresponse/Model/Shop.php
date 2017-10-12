<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Shop
 */
class GetresponseIntegration_Getresponse_Model_Shop extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('getresponse/shop');
    }

}