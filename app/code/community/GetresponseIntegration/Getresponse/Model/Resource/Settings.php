<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_Settings
 */
class GetresponseIntegration_Getresponse_Model_Resource_Settings extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('getresponse/settings', 'id');
	}
}