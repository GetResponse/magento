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
        if (!isset($resource['apiKey'], $resource['url'], $resource['domain'])) {
            throw ConnectionSettingsException::createForIncorrectSettings();
        }

        if (empty($resource['apiKey'])) {
            throw ConnectionSettingsException::createForIncorrectSettings();
        }

        return new ConnectionSettings(
            isset($resource['apiKey']) ? $resource['apiKey'] : '',
            isset($resource['url']) ? $resource['url'] : '',
            isset($resource['domain']) ? $resource['domain'] : ''
        );
    }

    /**
     * @param array $resource
     * @return ConnectionSettings
     */
    public static function createFromPost(array $resource)
    {
        if (isset($resource['is_mx']) && 1 === (int) $resource['is_mx']) {
            return new ConnectionSettings(
                isset($resource['apiKey']) ? $resource['apiKey'] : '',
                isset($resource['url']) ? $resource['url'] : '',
                isset($resource['domain']) ? $resource['domain'] : ''
            );
        }
        return new ConnectionSettings(
            isset($resource['apiKey']) ? $resource['apiKey'] : '',
            null,
            null
        );
    }
}
