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
            $resource['apiKey'],
            $resource['url'],
            $resource['domain']
        );
    }
}
