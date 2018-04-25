<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseException
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseException extends Exception
{
    const INVALID_JOB_TYPE = 100001;
    const API_KEY_NOT_FOUND = 100002;
    const SUBSCRIBER_NOT_FOUND = 100003;

    /**
     * @return GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
    public static function create_for_invalid_schedule_job_type()
    {
        return new self('Incorrect job type', self::INVALID_JOB_TYPE);
    }

    /**
     * @return GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
    public static function create_when_api_key_not_found()
    {
        return new self('Api key not found', self::API_KEY_NOT_FOUND);
    }

    /**
     * @return GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
    public static function create_when_subscriber_not_found()
    {
        return new self('Subscriber not found', self::SUBSCRIBER_NOT_FOUND);
    }
}
