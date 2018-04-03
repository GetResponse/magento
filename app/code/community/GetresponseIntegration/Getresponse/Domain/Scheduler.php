<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Scheduler
 */
class GetresponseIntegration_Getresponse_Domain_Scheduler
{
    /**
     * @param string $customer_id
     * @param string $type
     * @param array $payload
     *
     * @throws Exception
     */
    public function addToQueue($customer_id, $type, $payload)
    {
        $jobsQueue = Mage::getModel('getresponse/scheduleJobsQueue');
        $jobsQueue->setData([
            'customer_id' => $customer_id,
            'type' => $type,
            'payload' => json_encode($payload)
        ]);

        $jobsQueue->save();
    }
}
