<?php

class GetresponseIntegration_Getresponse_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Name library directory.
	 */
	const NAME_DIR_JS = 'getresponse/';

	/**
	 * List files for include.
	 *
	 * @var array
	 */
	protected $_files = array(
		'jquery-1.11.3.min.js',
		'jquery-ui.min.js',
		'jquery.noconflict.js',
		'getresponse-custom-field.src-verified.js',
		'jquery.switchButton.js',
		'getresponse_mapping.js',
        'autoresponder.js'
	);

    /**
     * @return string
     */
	public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->GetresponseIntegration_Getresponse->version;
    }

/**
	 * Check enabled.
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		if (isset($modulesArray['GetresponseIntegration_Getresponse'])) {
			return true;
		}

		return false;
	}

	/**
	 * Return path file.
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function getJQueryPath($file)
	{
		return self::NAME_DIR_JS . $file;
	}

	/**
	 * Return list files.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->_files;
	}

	public function getNewsletterSubscribersCollection()
    {
        return Mage::getModel('newsletter/subscriber')->getResourceCollection();
            //->addFieldToSelect('email');
    }

	/**
	 * @return mixed
	 */
	public function getCustomerCollection()
	{
		return Mage::getResourceModel('customer/customer_collection')
			->addAttributeToSelect('email')
			->addAttributeToSelect('firstname')
			->addAttributeToSelect('lastname')
			->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
			->joinAttribute('postcode', 'customer_address/city', 'default_billing', null, 'left')
			->joinAttribute('city', 'customer_address/postcode', 'default_billing', null, 'left')
			->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
			->joinAttribute('country', 'customer_address/country_id', 'default_billing', null, 'left')
			->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
			->joinAttribute('birthday', 'customer/dob', 'entity_id', null, 'left');
	}

	/**
	 * @return int
	 */
	public function getStoreId()
	{
		$store_id = Mage::app()->getStore()->getStoreId();

		return !empty($store_id) ? $store_id : Mage_Core_Model_App::DISTRO_STORE_ID;
	}

	/**
	 * @param $order
	 *
	 * @return array
	 */
	public function getCategoriesByOrder($order)
	{
		$categories = array();
		if ($order->getId()) {
			foreach ($order->getAllVisibleItems() as $item) {
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				$cats = $product->getCategoryIds();
				if ( !empty($cats)) {
					foreach ($cats as $cat) {
						$categories[] = $cat;
					}
				}
			}
		}

		return array_unique($categories);
	}

	/**
	 * disconnectIntegration
	 */
	public function disconnectIntegration($shop_id)
	{
		Mage::getModel('getresponse/settings')->disconnectSettings($shop_id);
		Mage::getModel('getresponse/account')->disconnectAccount($shop_id);
		Mage::getModel('getresponse/customs')->disconnectCustoms($shop_id);
		Mage::getModel('getresponse/webforms')->disconnectWebforms($shop_id);
		Mage::getModel('getresponse/automations')->disconnectAutomations($shop_id);
	}

}