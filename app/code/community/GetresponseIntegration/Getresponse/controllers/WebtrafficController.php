<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;

require_once Mage::getModuleDir(
        'controllers',
        'GetresponseIntegration_Getresponse'
    ) . DIRECTORY_SEPARATOR . 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_WebtrafficController
 */
class GetresponseIntegration_Getresponse_WebtrafficController
    extends GetresponseIntegration_Getresponse_BaseController
{

    /**
     * GET getresponse/webtraffic/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Web Traffic Tracking'))->_title(
            $this->__('GetResponse')
        );

        $this->_addContent(
            $this->getLayout()
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
        $hasActiveTrafficModule = (int)$this->getRequest()->getParam(
            'hasGrTrafficFeatureEnabled', 0
        );

        $settingsRepository = new SettingsRepository($this->currentShopId);
        $newSettings = SettingsFactory::createFromArray(
            array(
                'hasActiveTrafficModule' => $hasActiveTrafficModule
            )
        );
        $settingsRepository->update($newSettings);

        $message = $hasActiveTrafficModule == 0
            ? 'Web event traffic tracking disabled'
            : 'Web event traffic tracking enabled';

        $this->_getSession()->addSuccess($message);
        $this->_redirect('*/*/index');
    }

}