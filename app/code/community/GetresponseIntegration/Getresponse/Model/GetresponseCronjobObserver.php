<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;
use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;

/**
 * Class GetresponseIntegration_Getresponse_Model_ECommerceObserver
 */
class GetresponseIntegration_Getresponse_Model_GetresponseCronjobObserver
{
    public function exportJobsToGetresponse()
    {
        try {
            /** @var Mage_Sales_Model_Quote $quoteModel */
            $quoteModel = Mage::getModel('sales/quote');

            /** @var Mage_Sales_Model_Resource_Order $orderModel */
            $orderModel = Mage::getResourceModel('sales/order');

            $api = $this->buildApiInstance();

            $scheduler = new Scheduler();
            $cartHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler(
                $api
            );
            $orderHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler(
                $api
            );

            $customerHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler(
                $api
            );
            /** @var array $jobs */
            $jobs = $scheduler->getAllJobs();

            Mage::log(
                'CRONJOB: ilosc zadan: ' . count($jobs), 1, 'getresponse.log'
            );

            /** @var GetresponseIntegration_Getresponse_Model_ScheduleJobsQueue $job */
            foreach ($jobs as $job) {

                Mage::log(
                    'CRONJOB: send job: ' . $job->getData('type'), 1,
                    'getresponse.log'
                );

                switch ($job->getData('type')) {

                    case Scheduler::EXPORT_CUSTOMER:

                        $payload = json_decode($job->getData('payload'), true);

                        $customerHandler->sendCustomerToGetResponse(
                            $payload['campaign_id'],
                            $payload['cycle_day'],
                            $payload['gr_custom_fields'],
                            $payload['custom_fields'],
                            $payload['subscriber_email']
                        );

                        break;

                    case Scheduler::EXPORT_CART:

                        $payload = json_decode($job->getData('payload'), true);
                        Mage::app()->setCurrentStore($payload['shop_id']);

                        Mage::log(
                            'payload: ' . print_r($payload, 1), 1,
                            'getresponse.log'
                        );

                        /** @var Mage_Sales_Model_Quote $quote */
                        $quote = $quoteModel->load($payload['quote_id']);

                        $cartHandler->sendCartToGetresponse(
                            $quote,
                            $payload['campaign_id'],
                            $payload['subscriber_email'],
                            $payload['gr_store_id']
                        );

                        break;

                    case Scheduler::EXPORT_ORDER:

                        $payload = json_decode($job->getData('payload'), true);

                        /** @var Mage_Sales_Model_Order $order */
                        $order = Mage::getModel('sales/order')->load($payload['order_id']);

                        Mage::log(
                            'order instance: ' . get_class($order), 1,
                            'getresponse.log'
                        );

                        if ($order->isEmpty()) {
                            $job->delete();
                            break;
                        }

                        $quote = $quoteModel->load(
                            $order->getQuoteId()
                        );

                        $orderHandler->sendOrderToGetresponse(
                            $order,
                            $payload['subscriber_email'],
                            $payload['campaign_id'],
                            $quote->getData('getresponse_cart_id'),
                            $payload['gr_store_id'],
                            true
                        );

                        break;
                }

//                $job->delete();
            }
        } catch (Exception $e) {
            Mage::log(
                'Cannot process job - ' . $e, 1, 'getresponse.log'
            );
        }
    }

    /**
     * @return GetresponseIntegration_Getresponse_Helper_Api
     * @throws GetresponseException
     */
    private function buildApiInstance()
    {
        /** @var GetresponseIntegration_Getresponse_Helper_Data $getresponseHelper */
        $getresponseHelper = Mage::helper('getresponse');
        $shopId = $getresponseHelper->getStoreId();
        $getresponseSettings = new SettingsRepository($shopId);
        $accountSettings = $getresponseSettings->getAccount();

        if (empty($accountSettings['apiKey'])) {
            throw GetresponseException::create_when_api_key_not_found();
        }

        /** @var GetresponseIntegration_Getresponse_Helper_Api $api */
        $api = Mage::helper('getresponse/api');

        $api->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        return $api;
    }
}
