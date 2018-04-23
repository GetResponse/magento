<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder as GrCartBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder as GrOrderBuilder;
use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler as GrCustomerHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler as GrCartHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler as GrOrderHandler;

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_ExportController
 */
class GetresponseIntegration_Getresponse_ExportController extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/index/export
     *
     * @throws GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Export customers'))
            ->_title($this->__('GetResponse'));

        $this->prepareCustomsForMapping();

        $campaignDays = $this->api->getCampaignDays();
        $campaigns = $this->api->getCampaigns();
        $shops = $this->api->getShops();

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            array(
                'campaign_days' => $campaignDays
            )
        );

        /** @var Mage_Core_Block_Template $block */
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'getresponse_content');

        $block->setTemplate('getresponse/export.phtml')
            ->assign('campaign_days', $campaignDays)
            ->assign('campaigns', $campaigns)
            ->assign('gr_shops', $shops)
            ->assign('customs', $this->prepareCustomsForMapping())
            ->assign('autoresponder_block', $autoresponderBlock->toHtml());

        $this->_addContent($block);
        $this->renderLayout();
    }

    /**
     * POST getresponse/export/run
     *
     * @throws Exception
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
     *
     * @throws GetresponseIntegration_Getresponse_Domain_GetresponseException
     * @throws Exception
     */
    private function exportCustomers($campaignId, $params)
    {
        /** @var GetresponseIntegration_Getresponse_Helper_Api $api */
        $api = Mage::helper('getresponse/api');

        $cycleDay = '';
        $accountCustomFields = array_flip($api->getCustomFields());
        $grCustomFields = array_flip($accountCustomFields);
        $customFieldsToBeAdded = isset($params['gr_custom_field']) ?
            array_diff($params['gr_custom_field'], $accountCustomFields) : array();
        $failedCustomFields = array();
        $exportEcommerceEnabled = false;
        $storeId = '';
        $use_schedule = false;

        if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
            $cycleDay = (int) $params['cycle_day'];
        }

        if (isset($params['gr_export_ecommerce_details']) && 1 === (int)$params['gr_export_ecommerce_details']) {
            $exportEcommerceEnabled = true;
            $storeId = $params['ecommerce_store'];

            if (empty($storeId)) {
                $this->_getSession()->addError('You need to select a store');
                return;
            }
        }

        if (isset($params['gr_export_schedule']) && 1 === (int)$params['gr_export_schedule']) {
            $use_schedule = true;
        }

        $custom_fields = $this->prepareCustomFields(
            isset($params['gr_custom_field']) ? $params['gr_custom_field'] : array(),
            isset($params['custom_field']) ? $params['custom_field'] : array()
        );

        if (!empty($customFieldsToBeAdded)) {
            foreach ($customFieldsToBeAdded as $field_key => $field_value) {
                try {
                    $custom = $api->addCustomField($field_value);
                    $grCustomFields[$custom['name']] = $custom['customFieldId'];
                } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
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

        /** @var GetresponseIntegration_Getresponse_Helper_Data $getresponseHelper */
        $getresponseHelper = Mage::helper('getresponse');
        $shopId = $getresponseHelper->getStoreId();
        $scheduler = new Scheduler();
        $createCustomerHandler = new GrCustomerHandler($api);

        /** @var Mage_Newsletter_Model_Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {

            if ($use_schedule) {
                $scheduler->addToQueue(
                    $subscriber->getId(),
                    Scheduler::EXPORT_CUSTOMER,
                    array(
                        'campaign_id' => $campaignId,
                        'cycle_day' => $cycleDay,
                        'gr_custom_fields' => $grCustomFields,
                        'exportEcommerceEnabled' => $exportEcommerceEnabled,
                        'custom_fields' => $custom_fields,
                        'subscriber_email' => $subscriber->getEmail(),
                        'subscriber_id' => $subscriber->getId()
                    )
                );

                if (!$exportEcommerceEnabled) {
                    continue;
                }

                /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
                $orders = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', $subscriber->getCustomerId())
                    ->setOrder('created_at', 'desc');

                if (0 === $orders->count()) {
                    continue;
                }

                /** @var Mage_Sales_Model_Order $order */
                foreach ($orders as $order) {

                    $scheduler->addToQueue(
                        $subscriber->getId(),
                        Scheduler::EXPORT_CART,
                        array(
                            'quote_id' => $order->getQuoteId(),
                            'campaign_id' => $campaignId,
                            'subscriber_email' => $subscriber->getEmail(),
                            'gr_store_id' => $storeId,
                            'shop_id' => $shopId
                        )
                    );

                    $scheduler->addToQueue(
                        $subscriber->getId(),
                        Scheduler::EXPORT_ORDER,
                        array(
                            'order_id' => $order->getId(),
                            'campaign_id' => $campaignId,
                            'subscriber_email' => $subscriber->getEmail(),
                            'gr_store_id' => $storeId,
                            'shop_id' => $shopId
                        )
                    );
                }

            } else {
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
                    ->addFieldToFilter('customer_id', $subscriber->getCustomerId())
                    ->setOrder('created_at', 'desc');

                if (0 === $orders->count()) {
                    continue;
                }

                /** @var Mage_Sales_Model_Quote $salesQuote */
                $salesQuote = Mage::getModel('sales/quote');

                $createCartHandler = new GrCartHandler(
                    $api,
                    $salesQuote,
                    new GrCartBuilder(),
                    new GrProductHandler($this->api)
                );

                $createOrderHandler = new GrOrderHandler(
                    $api,
                    new GrProductHandler($api),
                    new GrOrderBuilder()
                );

                /** @var Mage_Sales_Model_Order $order */
                foreach ($orders as $order) {

                    $cartId = $createCartHandler->sendCartToGetresponseFromOrder(
                        $order,
                        $campaignId,
                        $subscriber->getEmail(),
                        $storeId
                    );

                    if (empty($cartId)) {
                        GetresponseIntegration_Getresponse_Helper_Logger::log('Cart not found for order: ' . $order->getId());
                        continue;
                    }

                    $createOrderHandler->sendOrderToGetresponse(
                        $order,
                        $subscriber->getEmail(),
                        $campaignId,
                        $cartId,
                        $storeId,
                        true
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
        $fields = array();

        foreach ($grCustomFields as $id => $name) {
            $fields[$name] = isset($customFields[$id]) ? $customFields[$id] : null;
        }

        return $fields;
    }
}
