<?php

class GetresponseIntegration_Getresponse_Block_Getresponse extends Mage_Core_Block_Template
{
	/**
	 * Constructor. Set template.
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('getresponse/webform.phtml');
	}

}
