<?php
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory as CustomFieldsCollectionFactory;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository as CustomFieldsCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_CustomFieldFactory as CustomFieldFactory;

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

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            array(
                'campaign_days' => $this->api->getCampaignDays(),
                'selected_day' => $this->settings->api['cycleDay']
            )
        );

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/viapage.phtml')
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('customs', $this->prepareCustomsForMapping())
            ->assign('settings', $this->settings)
            ->assign('autoresponder_block', $autoresponderBlock->toHtml())
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/subscription/save
     */
    public function saveAction()
    {
        $this->_initAction();
        $campaignId = $this->getRequest()->getParam('campaign_id', 0);
        $activeSubscription = $this->getRequest()->getParam('active_subscription', 0);
        $syncOrderData = $this->getRequest()->getParam('gr_sync_order_data', 0);
        $subscriptionOnCheckout = $this->getRequest()->getParam('subscription_on_checkout', 0);
        $autoresponder = (int)$this->getRequest()->getParam('gr_autoresponder', 0);
        $cycleDay = $this->getRequest()->getParam('cycle_day', null);

        $params = $this->getRequest()->getParams();

        if ($activeSubscription === 1 && empty($campaignId)) {
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

        $settingsRepository = new SettingsRepository($this->currentShopId);
        $oldSettings = $settingsRepository->getAccount();

        if (1 !== $autoresponder) {
            $cycleDay = NULL;
        }

        if ($activeSubscription === 0) {
            $newSettings = SettingsFactory::createFromArray(
                [
                    'campaignId' => 0,
                    'activeSubscription' => 0,
                    'updateAddress' => 0,
                    'cycleDay' => null,
                    'subscriptionOnCheckout' => 0,
                    'newsletterCycleDay' => $oldSettings['newsletterCycleDay']
                ]
            );
        } else {
            $newSettings = SettingsFactory::createFromArray(
                [
                    'campaignId' => $campaignId,
                    'activeSubscription' => $activeSubscription,
                    'updateAddress' => $syncOrderData,
                    'cycleDay' => $cycleDay,
                    'subscriptionOnCheckout' => $subscriptionOnCheckout,
                    'newsletterCycleDay' => $oldSettings['newsletterCycleDay']
                ]
            );
        }

        $settingsRepository->update($newSettings);

        if (!empty($params['gr_sync_order_data']) && isset($params['gr_custom_field'])) {

            $customMap = [];
            $customsDb = $this->settings->customs;

            foreach ($params['gr_custom_field'] as $key => $name) {
                $customMap[$params['custom_field'][$key]] = $name;
            }

            foreach ($customsDb as $key => $cf) {
                if (isset($customMap[$cf['custom_field']])) {
                    $customsDb[$key]['custom_active'] = GetresponseIntegration_Getresponse_Model_Customs::ACTIVE;
                    $customsDb[$key]['custom_value'] = $customMap[$cf['custom_field']];
                } else {
                    $cf['custom_active'] = GetresponseIntegration_Getresponse_Model_Customs::INACTIVE;
                }
            }

            $customFieldsCollectionRepository = new CustomFieldsCollectionRepository($this->currentShopId);
            $customFieldsCollection = CustomFieldsCollectionFactory::createFromArray(array());
            foreach ($customsDb as $custom) {
                $customTemp = CustomFieldFactory::createFromArray(array(
                        'id' => $custom['id_custom'],
                        'customField' => $custom['custom_field'],
                        'customValue' => $custom['custom_value'],
                        'isDefault' => $custom['default'],
                        'isActive' => $custom['custom_active']
                    )
                );
                $customFieldsCollection->add($customTemp);
            };
            $customFieldsCollectionRepository->create($customFieldsCollection);
        }
        $this->_getSession()->addSuccess('Settings saved');
        $this->_redirect('*/*/index');
    }
}