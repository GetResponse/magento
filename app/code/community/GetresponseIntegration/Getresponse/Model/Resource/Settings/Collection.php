<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_Settings_Collection
 */
class GetresponseIntegration_Getresponse_Model_Resource_Settings_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('getresponse/settings');
	}
}