<?php

/**
 * Class GetresponseIntegration_Getresponse_Customs
 */
class GetresponseIntegration_Getresponse_Model_Resource_Customs extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * Construct
	 */
	protected function _construct()
	{
		$this->_init('getresponse/customs', 'id_custom');
	}
}