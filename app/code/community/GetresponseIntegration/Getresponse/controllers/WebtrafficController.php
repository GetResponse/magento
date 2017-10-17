<?php

require_once Mage::getModuleDir('controllers', 'GetresponseIntegration_Getresponse').DIRECTORY_SEPARATOR.'BaseController.php';

class GetresponseIntegration_Getresponse_WebtrafficController extends GetresponseIntegration_Getresponse_BaseController
{

    /**
     * GET getresponse/webtraffic/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Web Traffic Tracking'))->_title($this->__('GetResponse'));

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/webtraffic.phtml')
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/webtraffic/save
     */
    public function saveAction()
    {
        $this->_initAction();
        $hasActiveTrafficModule = (int)$this->getRequest()->getParam('has_active_traffic_module', 0);

        Mage::getModel('getresponse/settings')->updateSettings(
            array('has_active_traffic_module' => $hasActiveTrafficModule),
            $this->currentShopId
        );

        $message = $hasActiveTrafficModule == 0 ? 'Web event traffic tracking disabled' : 'Web event traffic tracking enabled';

        $this->_getSession()->addSuccess($message);
        $this->_redirect('*/*/index');
    }

}