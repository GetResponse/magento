<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_Webforms
 */
class GetresponseIntegration_Getresponse_Model_Resource_Webforms extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('getresponse/webforms', 'id');
	}
}