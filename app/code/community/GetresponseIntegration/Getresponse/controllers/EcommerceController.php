<?php

use GetresponseIntegration_Getresponse_Domain_ShopRepository as ShopRepository;
use GetresponseIntegration_Getresponse_Domain_ShopFactory as ShopFactory;

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_EcommerceController
    extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/ecommerce/index
     */
    public function indexAction()
    {
        $this->_initAction();

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $this->_title($this->__('Shop'))->_title($this->__('GetResponse'));
        $shopRepository = new ShopRepository($this->currentShopId);
        $ecommerceSettings = $shopRepository->getShop()->toArray();

        /** @var Mage_Core_Block_Template $block */
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template', 'getresponse_content'
        );

        try {
            $shops = $this->api->getShops();
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            $shops = array();
        }

        $this->_addContent(
            $block
                ->setTemplate('getresponse/shop.phtml')
                ->assign('gr_shops', $shops)
                ->assign(
                    'shop_enabled', isset($ecommerceSettings['isEnabled'])
                    ? $ecommerceSettings['isEnabled'] : 0
                )
                ->assign(
                    'current_shop_id', isset($ecommerceSettings['grShopId'])
                    ? $ecommerceSettings['grShopId'] : null
                )
                ->assign(
                    'schedule_optimization',
                    isset($ecommerceSettings['isScheduleOptimizationEnabled'])
                        ? $ecommerceSettings['isScheduleOptimizationEnabled']
                        : 0
                )
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/ecommerce/save
     */
    public function saveAction()
    {
        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $isEnabled = (int)$this->getRequest()->getParam('shop_enabled', 0);
        $shopId = $this->getRequest()->getParam('shop_id');
        $isScheduleOptimizationEnabled = $this->getRequest()->getParam(
            'schedule_optimization'
        );
        $shopRepository = new ShopRepository($this->currentShopId);

        if (empty($shopId)) {
            $this->_getSession()->addError(
                'You first need to select a store you want to send ecommerce data from'
            );
            $this->_redirect('*/*/index');

            return;
        }

        if (1 === $isEnabled) {
            $data = array(
                'isEnabled'                     => 1,
                'grShopId'                      => $shopId,
                'isScheduleOptimizationEnabled' => $isScheduleOptimizationEnabled
            );

            $dataToUpdate = ShopFactory::createFromArray($data);
            $shopRepository->create($dataToUpdate);
        } else {
            $shopRepository->delete();
        }

        $this->_getSession()->addSuccess('Ecommerce settings saved');
        $this->_redirect('*/*/index');
    }

    /**
     * AJAX POST getresponse/ecommerce/add_shop
     */
    public function add_shopAction()
    {
        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $this->_initAction();
        $shopName = $this->getRequest()->getParam('name');

        if (0 === strlen($shopName)) {
            $data = array(
                'type' => 'error',
                'msg'  => 'Shop name is incorrect.'
            );

            Mage::app()->getResponse()->setBody(
                json_encode($data, JSON_PRETTY_PRINT)
            );

            return;
        }

        try {
            $shop = $this->api->addShop($shopName);
            $data = array(
                'result'    => 'success',
                'msg'       => 'Shop ' . $shop['name']
                    . ' successfully created.',
                'shop_id'   => $shop['shopId'],
                'shop_name' => $shop['name']
            );
        } catch (\Exception $e) {
            $data = array(
                'result' => 'error',
                'msg'    => 'Shop has not been created.',
            );
        }
        Mage::app()->getResponse()->setBody(
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * AJAX POST getresponse/ecommerce/delete_shop
     */
    public function delete_shopAction()
    {
        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $this->_initAction();

        if (false === $this->api->deleteShop(
                $this->getRequest()->getParam('id')
            )
        ) {
            $data = array('result' => 'error');
        } else {
            $data = array('result' => 'success');
        }
        Mage::app()->getResponse()->setBody(
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
