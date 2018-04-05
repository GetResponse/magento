<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseException
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseException extends Exception
{
    const INVALID_JOB_TYPE = 100001;
    /**
     * @return GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
    public static function create_for_invalid_schedule_job_type()
    {
        return new self('Incorrect job type', self::INVALID_JOB_TYPE);
    }
}