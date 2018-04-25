<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Resource_ScheduleJobsQueue
 */
class GetresponseIntegration_Getresponse_Model_Resource_ScheduleJobsQueue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('getresponse/scheduleJobsQueue', 'id');
    }
}
