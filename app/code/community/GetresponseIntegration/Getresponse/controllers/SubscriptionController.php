<?php

require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

class GetresponseIntegration_Getresponse_SubscriptionController extends GetresponseIntegration_Getresponse_BaseController
{

    /**
     * GET getresponse/subscription/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Subscription via registration page'))->_title($this->__('GetResponse'));

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/viapage.phtml')
            ->assign('campaign_days', $this->api->getCampaignDays())
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('customs', $this->prepareCustomsForMapping())
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/subscription/save
     */
    public function saveAction()
    {
        $this->_initAction();

        $campaignId = $this->getRequest()->getParam('campaign_id');
        $activeSubscription = $this->getRequest()->getParam('active_subscription', 0);
        $syncOrderData = $this->getRequest()->getParam('gr_sync_order_data', 0);
        $subscriptionOnCheckout = $this->getRequest()->getParam('subscription_on_checkout', 0);
        $autoresponder = $this->getRequest()->getParam('gr_autoresponder', 0);
        $cycleDay = $this->getRequest()->getParam('cycle_day', 0);

        $params = $this->getRequest()->getParams();

        if (empty($campaignId)) {
            $this->_getSession()->addError('You need to select list');
            $this->_redirect('*/*/index');
            return;
        }

        if (!empty($params['gr_custom_field'])) {
            foreach ($params['gr_custom_field'] as $field_key => $field_value) {
                if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', ($field_value))) {
                    $this->_getSession()->addError('Incorrect field name: ' . $field_value . '.');
                    $this->_redirect('*/*/index');
                    return;
                }
            }
        }

        if (1 !== $autoresponder) {
            $cycleDay = 0;
        }

        Mage::getModel('getresponse/settings')->updateSettings(
            [
                'campaign_id' => $campaignId,
                'active_subscription' => $activeSubscription,
                'update_address' => $syncOrderData,
                'cycle_day' => $cycleDay,
                'subscription_on_checkout' => $subscriptionOnCheckout
            ],
            $this->currentShopId
        );

        if (!empty($params['gr_sync_order_data']) && isset($params['gr_custom_field'])) {

            $customMap = [];

            foreach ($params['gr_custom_field'] as $key => $name) {
                $customMap[$name] = $params['custom_field'][$key];
            }

            foreach ($this->settings->customs as $cf) {
                if (isset($customMap[$cf['custom_field']])) {

                    Mage::getModel('getresponse/customs')->updateCustom(
                        $cf['id_custom'],
                        [
                            'custom_value' => $customMap[$cf['custom_field']],
                            'active_custom' => GetresponseIntegration_Getresponse_Model_Customs::ACTIVE
                        ]
                    );
                } else {
                    Mage::getModel('getresponse/customs')->updateCustom(
                        $cf['id_custom'],
                        ['active_custom' => GetresponseIntegration_Getresponse_Model_Customs::INACTIVE]
                    );
                }
            }
        }

        $this->_getSession()->addSuccess('Settings saved');
        $this->_redirect('*/*/index');
    }
}