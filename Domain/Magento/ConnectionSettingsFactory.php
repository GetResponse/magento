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
     * @throws ConnectionSettingsException
     */
    public static function createFromArray(array $resource)
    {

        if (!isset($resource['apiKey'])) {
            throw ConnectionSettingsException::createForIncorrectSettings();
        }
        if (empty($resource['apiKey'])) {
            throw ConnectionSettingsException::createForIncorrectSettings();
        }
        return new ConnectionSettings($resource['apiKey'], $resource['url'], $resource['domain']);
    }

    /**
     * @param array $resource
     * @return ConnectionSettings
     */
    public static function createFromPost(array $resource)
    {
        if (isset($resource['is_mx']) && 1 === (int) $resource['is_mx']) {
            return new ConnectionSettings($resource['apiKey'], $resource['url'], $resource['domain']);
        }
        return new ConnectionSettings($resource['apiKey'], '', '');
    }
}
