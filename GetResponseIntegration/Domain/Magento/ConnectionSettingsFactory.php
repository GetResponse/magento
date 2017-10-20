<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class ConnectionSettingsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class ConnectionSettingsFactory
{
    /**
     * @param string $apiKey
     * @param string $url
     * @param string $domain
     *
     * @return ConnectionSettings
     */
    public static function buildFromUserPayload($apiKey, $url, $domain)
    {
        return new ConnectionSettings($apiKey, $url , $domain);
    }

    /**
     * @param array $resource
     *
     * @return ConnectionSettings
     */
    public static function buildFromRepository(array $resource)
    {

        return new ConnectionSettings(
            $resource['apiKey'],
            $resource['url'],
            $resource['domain']
        );
    }
}
