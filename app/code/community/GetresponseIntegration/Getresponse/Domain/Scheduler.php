<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;

/**
 * Class GetresponseIntegration_Getresponse_Domain_Scheduler
 */
class GetresponseIntegration_Getresponse_Domain_Scheduler
{
    const UPDATE_CART = 'update_cart';
    const REMOVE_CART = 'remove_cart';
    const CREATE_ORDER = 'update_order';
    const CREATE_CUSTOMER = 'create_customer';

    static $VALID_TYPES = [
        self::UPDATE_CART,
        self::REMOVE_CART,
        self::CREATE_ORDER,
        self::CREATE_CUSTOMER
    ];

    /**
     * @param string $customer_id
     * @param string $type
     * @param array $payload
     *
     * @throws Exception
     */
    public function addToQueue($customer_id, $type, $payload)
    {
        if (!in_array($type, self::$VALID_TYPES)) {
            throw GetresponseException::create_for_invalid_schedule_job_type();
        }

        $jobsQueue = Mage::getModel('getresponse/scheduleJobsQueue');
        $jobsQueue->setData([
            'customer_id' => $customer_id,
            'type' => $type,
            'payload' => json_encode($payload)
        ]);

        $jobsQueue->save();
    }

    /**
     * @return GetresponseIntegration_Getresponse_Model_ScheduleJobsQueue[]
     */
    public function getAllJobs()
    {
        $jobsQueueCollection = Mage::getModel('getresponse/scheduleJobsQueue')->getCollection();
        return $jobsQueueCollection->getItems();
    }
}
