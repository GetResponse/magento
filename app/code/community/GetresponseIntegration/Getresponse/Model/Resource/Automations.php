<?php

/**
 * Class GetresponseIntegration_Getresponse_Automations
 */
class GetresponseIntegration_Getresponse_Model_Resource_Automations extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * Construct
	 */
	protected function _construct()
	{
		$this->_init('getresponse/automations', 'id');
	}
}