<?php

require_once __DIR__ . '/BaseController.php';

use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler as GrCustomerHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler as GrCartHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler as GrOrderHandler;

/**
 * Class GetresponseIntegration_Getresponse_ExportController
 */
class GetresponseIntegration_Getresponse_ExportController extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/index/export
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Export customers'))
            ->_title($this->__('GetResponse'));

        $this->prepareCustomsForMapping();

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            [
                'campaign_days' => $this->api->getCampaignDays()
            ]
        );

        /** @var Mage_Core_Block_Template $block */
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'getresponse_content');

        $block->setTemplate('getresponse/export.phtml')
            ->assign('campaign_days', $this->api->getCampaignDays())
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('gr_shops', (array)$this->api->getShops())
            ->assign('customs', $this->prepareCustomsForMapping())
            ->assign('autoresponder_block', $autoresponderBlock->toHtml());

        $this->_addContent($block);
        $this->renderLayout();
    }

    /**
     * POST getresponse/export/run
     */
    public function runAction()
    {
        $this->_initAction();

        $campaign_id = $this->getRequest()->getParam('campaign_id');

        if (empty($campaign_id)) {
            $this->_getSession()->addError('List can\'t be empty');
            $this->_redirect('*/*/index');

            return;
        }

        $this->exportCustomers($campaign_id, $this->getRequest()->getParams());
        $this->_redirect('*/*/index');
    }

    /**
     * @param $campaignId
     * @param $params
     */
    private function exportCustomers($campaignId, $params)
    {
        /** @var GetresponseIntegration_Getresponse_Helper_Api $api */
        $api = Mage::helper('getresponse/api');

        $cycleDay = '';
        $accountCustomFields = array_flip($api->getCustomFields());
        $grCustomFields = array_flip($accountCustomFields);
        $customFieldsToBeAdded = isset($params['gr_custom_field']) ?
            array_diff($params['gr_custom_field'], $accountCustomFields) : [];
        $failedCustomFields = [];
        $exportEcommerceEnabled = false;
        $storeId = '';
        $use_schedule = false;

        if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
            $cycleDay = (int) $params['cycle_day'];
        }

        if (isset($params['gr_export_ecommerce_details']) && 1 === (int)$params['gr_export_ecommerce_details']) {
            $exportEcommerceEnabled = true;
            $storeId = $params['ecommerce_store'];
        }

        if (isset($params['gr_export_schedule']) && 1 === (int)$params['gr_export_schedule']) {
            $use_schedule = true;
        }

        $custom_fields = $this->prepareCustomFields(
            isset($params['gr_custom_field']) ? $params['gr_custom_field'] : [],
            isset($params['custom_field']) ? $params['custom_field'] : []
        );

        if (!empty($customFieldsToBeAdded)) {
            foreach ($customFieldsToBeAdded as $field_key => $field_value) {
                $custom = $api->addCustomField($field_value);
                $grCustomFields[$custom->name] = $custom->customFieldId;
                if (!isset($custom->customFieldId)) {
                    $failedCustomFields[] = $field_value;
                }
            }
            if (!empty($failedCustomFields)) {
                $this->_getSession()->addError('Incorrect field name: ' . implode(', ', $failedCustomFields) . '.');

                return;
            }
        }

        /** @var GetresponseIntegration_Getresponse_Helper_Data $helperData */
        $helperData = Mage::helper('getresponse');
        $subscribers = $helperData->getNewsletterSubscribersCollection();

        if (empty($subscribers)) {
            $this->_getSession()->addError('Customers not found');
            return;
        }

        /** @var Mage_Newsletter_Model_Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {

            if ($use_schedule) {

                $scheduler = new Scheduler();
                $scheduler->addToQueue(
                    $subscriber->getId(),
                    Scheduler::CREATE_CUSTOMER,
                    [
                        'campaign_id' => $campaignId,
                        'cycle_day' => $cycleDay,
                        'gr_custom_fields' => $grCustomFields,
                        'exportEcommerceEnabled' => $exportEcommerceEnabled,
                        'custom_fields' => $custom_fields,
                        'subscriber_email' => $subscriber->getEmail(),
                        'subscriber_id' => $subscriber->getId()
                    ]
                );
            } else {
                $createCustomerHandler = new GrCustomerHandler();
                $createCustomerHandler->sendCustomerToGetResponse(
                    $campaignId,
                    $cycleDay,
                    $grCustomFields,
                    $custom_fields,
                    $subscriber->getEmail()
                );

                if (!$exportEcommerceEnabled) {
                    continue;
                }

                /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
                $orders = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', $subscriber->getId())
                    ->setOrder('created_at', 'desc');

                if (0 === $orders->count()) {
                    continue;
                }

                /** @var Mage_Sales_Model_Order $order */
                foreach ($orders as $order) {

                    $createCartHandler = new GrCartHandler($storeId);
                    $cartId = $createCartHandler->sendCartToGetresponse(
                        $order,
                        $campaignId,
                        $subscriber->getEmail()
                    );

                    if (empty($cartId)) {
                        Mage::log('Cart not created', 1, 'getresponse.log');
                        continue;
                    }

                    $createOrderHandler = new GrOrderHandler($storeId);
                    $createOrderHandler->sendOrderToGetresponse(
                        $order,
                        $subscriber->getEmail(),
                        $campaignId,
                        $cartId
                    );
                }
            }
        }

        $this->_getSession()->addSuccess('Customer data exported');
    }

    /**
     * @param array $grCustomFields
     * @param array $customFields
     *
     * @return array
     */
    private function prepareCustomFields($grCustomFields, $customFields)
    {
        $fields = [];

        foreach ($grCustomFields as $id => $name) {
            $fields[$name] = isset($customFields[$id]) ? $customFields[$id] : null;
        }

        return $fields;
    }

}