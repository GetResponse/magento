<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_Account
 */
class GetresponseIntegration_Getresponse_Model_Resource_Account extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('getresponse/account', 'id');
	}
}