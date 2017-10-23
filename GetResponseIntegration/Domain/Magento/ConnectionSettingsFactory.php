<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseRepositoryException;

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
     * @return ConnectionSettings
     * @throws GetResponseRepositoryException
     */
    public static function buildFromRepository(array $resource)
    {
        if (empty($resource['apiKey'])) {
            throw GetResponseRepositoryException::buildForInvalidApiKey();
        }

        return new ConnectionSettings(
            $resource['apiKey'],
            $resource['url'],
            $resource['domain']
        );
    }
}
