<?php

/**
 * Class GetresponseIntegration_Getresponse_Helper_Logger
 */
class GetresponseIntegration_Getresponse_Helper_Logger extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $message
     */
    public static function log($message)
    {
        Mage::log($message, 7, 'getresponse.log');
    }

    /**
     * @param Exception $exception
     */
    public static function logException($exception)
    {
        self::log($exception->getMessage() . ' [ErrorCode:'. $exception->getCode() .']');
    }
}
