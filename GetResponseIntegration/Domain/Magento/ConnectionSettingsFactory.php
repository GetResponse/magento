<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class ConnectionSettingsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class ConnectionSettingsFactory
{
    /**
     * @param array $resource
     * @return ConnectionSettings
     */
    public static function createFromArray(array $resource)
    {
        return new ConnectionSettings(
            isset($resource['apiKey']) ? $resource['apiKey'] : '',
            isset($resource['url']) ? $resource['url'] : '',
            isset($resource['domain']) ? $resource['domain'] : ''
        );
    }
}
