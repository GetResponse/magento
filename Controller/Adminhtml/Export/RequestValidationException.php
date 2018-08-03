<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

/**
 * Class RequestValidationException
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class RequestValidationException extends \Exception
{
    /**
     * @param string $message
     * @return RequestValidationException
     */
    public static function createWithMessage($message)
    {
        return new self($message);
    }
}