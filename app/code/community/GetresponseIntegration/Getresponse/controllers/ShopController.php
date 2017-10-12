<?php

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_ShopController extends Mage_Adminhtml_Controller_Action
{
    public $current_shop_id;
    public $settings;
    public $shop_settings;

	/**
	 * construct
	 */
	protected function _construct()
	{
		$this->current_shop_id = Mage::helper('getresponse')->getStoreId();
	}

    protected function _isAllowed()
    {
        return true;
    }

	/**
	 * Main init action, et layout and template
	 *
	 * @return $this
	 */
	protected function _initAction()
	{
		$this->settingsHandler();

		$this->loadLayout()->_setActiveMenu('getresponse_menu/settings_page');

		if ($this->active_tab != 'index' && empty($this->settings->api['api_key'])) {
			Mage::getSingleton('core/session')
					->addError('Access denied - module is not connected to GetResponse Account.');
			$this->_redirect('getresponse/index/index');
		}

		return $this;
	}

	/**
	 * Main extenction settings
	 */
	protected function settingsHandler()
	{
		$this->settings->main['api_url_360_com'] = 'https://api3.getresponse360.com/v3';
		$this->settings->main['api_url_360_pl'] = 'https://api3.getresponse360.pl/v3';

		$this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();

        $this->shop_settings = Mage::getModel('getresponse/shop')->load($this->current_shop_id)->getData();

		Mage::helper('getresponse/api')->setApiDetails(
            $this->settings->api['api_key'],
            $this->settings->api['api_url'],
            $this->settings->api['api_domain']
		);
	}

	/**
	 * GET getresponse/shop/index
	 */
	public function indexAction()
	{
		$this->_title($this->__('Shop'))->_title($this->__('GetResponse'));

		$this->active_tab = 'shop';

		$this->_initAction();

        $gr_shops = Mage::helper('getresponse/api')->getShops();

        $hasActiveRegistrationSubscription = true;

        if (empty($this->settings->api['campaign_id']) || $this->settings->api['active_subscription'] == 0) {
            $hasActiveRegistrationSubscription = false;
        }

        //print_r($this->shop_settings);die;

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/shop.phtml')
				->assign('gr_shops', (array) $gr_shops)
                ->assign('shop_enabled', isset($this->shop_settings['is_enabled']) ? $this->shop_settings['is_enabled'] : 0)
                ->assign('current_shop_id', isset($this->shop_settings['gr_shop_id']) ? $this->shop_settings['gr_shop_id'] : null)
                ->assign('has_active_registration_subscription', $hasActiveRegistrationSubscription)
		);

		$this->renderLayout();
	}

    /**
     * POST getresponse/shop/update
     */
    public function updateAction()
    {
        $shopEnable = $this->getRequest()->getParam('shop_enabled');
        $shopId = $this->getRequest()->getParam('shop_id');

        if (empty($shopId)) {
            Mage::getSingleton('core/session')->addError('Incorrect shop, please try again.');
            $this->_redirect('getresponse/shop/index');
            return;
        }

        $data = array(
            'is_enabled' => empty($shopEnable) ? 0 : 1,
            'gr_shop_id' => $shopId,
            'shop_id' => $this->current_shop_id,
        );

        try {

            Mage::getModel('getresponse/shop')
                ->load($this->current_shop_id)
                ->setData($data)
                ->save();

            Mage::getSingleton('core/session')->addSuccess('Ecommerce settings saved');

        } catch (Exception $e) {
            Mage::helper('getresponse/logger')->logException($e);
            Mage::helper('getresponse/logger')->log('Unable to upsert shop settings');
            Mage::getSingleton('core/session')->addError('Error during settings details save: ' . $e->getMessage());
        }

        $this->_redirect('getresponse/shop/index');

	}

    /**
     * AJAX POST getresponse/shop/add
     */
    public function addAction()
    {
        $shopName = $this->getRequest()->getParam('name');

        if (0 === strlen($shopName)) {
            $data = array(
                'type' => 'error',
                'msg' => 'Shop name is incorrect.'
            );

            Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        $this->settingsHandler();

        /** @var GetresponseIntegration_Getresponse_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('getresponse/api');

        try {
            $shop = $apiHelper->addShop($shopName);

            $data = array(
                'result' => 'success',
                'msg' => 'Shop '.$shop->name.' successfully created.',
                'shop_id' => $shop->shopId,
                'shop_name' => $shop->name
            );

        } catch (\Exception $e) {
            $data = array(
                'result' => 'error',
                'msg' => 'Shop has not been created.',
            );
        }
        Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
	}

    /**
     * AJAX POST getresponse/shop/delete
     */
	public function deleteAction()
    {
        $this->settingsHandler();

        /** @var GetresponseIntegration_Getresponse_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('getresponse/api');

        if (false === $apiHelper->deleteShop($this->getRequest()->getParam('id'))) {
            $data = array('result' => 'error');
        } else {
            $data = array('result' => 'success');
        }
        Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
    }
}