<?php

require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_EcommerceController extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/ecommerce/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Shop'))->_title($this->__('GetResponse'));

        $ecommerceSettings = Mage::getModel('getresponse/shop')->load($this->currentShopId)->getData();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/shop.phtml')
            ->assign('gr_shops', (array)$this->api->getShops())
            ->assign('shop_enabled', isset($ecommerceSettings['is_enabled']) ? $ecommerceSettings['is_enabled'] : 0)
            ->assign('current_shop_id',
                isset($ecommerceSettings['gr_shop_id']) ? $ecommerceSettings['gr_shop_id'] : null)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/ecommerce/save
     */
    public function saveAction()
    {
        $isEnabled = (int)$this->getRequest()->getParam('shop_enabled', 0);
        $shopId = $this->getRequest()->getParam('shop_id');

        if (empty($shopId)) {
            $this->_getSession()->addError('You first need to select a store you want to send ecommerce data from');
            $this->_redirect('*/*/index');
            return;
        }

        if (1 === $isEnabled) {
            $data = [
                'is_enabled' => 1,
                'gr_shop_id' => $shopId,
                'shop_id' => $this->currentShopId,
            ];

            Mage::getModel('getresponse/shop')
                ->load($this->currentShopId)
                ->setData($data)
                ->save();
        } else {
            Mage::getModel('getresponse/shop')->disconnect($this->currentShopId);
        }

        $this->_getSession()->addSuccess('Ecommerce settings saved');

        $this->_redirect('*/*/index');

    }

    /**
     * AJAX POST getresponse/ecommerce/add_shop
     */
    public function add_shopAction()
    {
        $this->_initAction();
        $shopName = $this->getRequest()->getParam('name');

        if (0 === strlen($shopName)) {
            $data = [
                'type' => 'error',
                'msg' => 'Shop name is incorrect.'
            ];

            Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        try {
            $shop = $this->api->addShop($shopName);
            $data = [
                'result' => 'success',
                'msg' => 'Shop ' . $shop->name . ' successfully created.',
                'shop_id' => $shop->shopId,
                'shop_name' => $shop->name
            ];
        } catch (\Exception $e) {
            $data = [
                'result' => 'error',
                'msg' => 'Shop has not been created.',
            ];
        }
        Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * AJAX POST getresponse/ecommerce/delete_shop
     */
    public function delete_shopAction()
    {
        $this->_initAction();

        if (false === $this->api->deleteShop($this->getRequest()->getParam('id'))) {
            $data = ['result' => 'error'];
        } else {
            $data = ['result' => 'success'];
        }
        Mage::app()->getResponse()->setBody(json_encode($data, JSON_PRETTY_PRINT));
    }
}