<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_NewsletterController
 */
class GetresponseIntegration_Getresponse_NewsletterController
    extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/newsletter/index
     */
    public function indexAction()
    {
        $this->_initAction();

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $this->_title($this->__('Subscription via newsletter'))->_title(
            $this->__('GetResponse')
        );

        try {
            $campaignDays = $this->api->getCampaignDays();
            $campaigns = $this->api->getCampaigns();
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            $campaignDays = $campaigns = array();
        }

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            array(
                'campaign_days' => $campaignDays,
                'selected_day'  => $this->settings->api['newsletterCycleDay']
            )
        );

        $this->_addContent(
            $this->getLayout()
                ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
                ->setTemplate('getresponse/newsletter.phtml')
                ->assign('settings', $this->settings)
                ->assign('campaigns', $campaigns)
                ->assign('autoresponder_block', $autoresponderBlock->toHtml())
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/newsletter/save
     */
    public function saveAction()
    {
        $this->_initAction();

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $isEnabled = (int)$this->getRequest()->getParam(
            'newsletter_subscription', 0
        );
        $newsletterCampaignId = $this->getRequest()->getParam(
            'newsletter_campaign_id', 0
        );
        $newsletterCycleDay = (int)$this->getRequest()->getParam(
            'cycle_day', null
        );
        $isAutoresponderEnabled = (int)$this->getRequest()->getParam(
            'gr_autoresponder', 0
        );

        if ($isEnabled === 1 && empty($newsletterCampaignId)) {
            $this->_getSession()->addError('You need to select list');
            $this->_redirect('*/*/index');

            return;
        }

        if (0 === $isEnabled) {
            $newsletterCampaignId = 0;
            $newsletterCycleDay = null;
        } else {
            $newsletterCycleDay = (0 === $isAutoresponderEnabled) ? null
                : $newsletterCycleDay;
        }

        $settingsRepository = new SettingsRepository($this->currentShopId);
        $oldSettings = $settingsRepository->getAccount();
        $newSettings = SettingsFactory::createFromArray(
            array(
                'newsletterSubscription' => $isEnabled,
                'newsletterCampaignId'   => $newsletterCampaignId,
                'newsletterCycleDay'     => $newsletterCycleDay,
                'cycleDay'               => $oldSettings['cycleDay']
            )
        );

        $settingsRepository->update($newSettings);

        $this->_getSession()->addSuccess('Settings saved');
        $this->_redirect('*/*/index');
    }

}