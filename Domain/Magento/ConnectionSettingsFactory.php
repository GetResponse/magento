<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class ConnectionSettingsFactory
{
    public static function createFromArray(array $resource): ConnectionSettings
    {
        return new ConnectionSettings($resource['apiKey'], $resource['url'], $resource['domain']);
    }

    public static function createFromPost(array $resource): ConnectionSettings
    {
        if (isset($resource['is_mx']) && 1 === (int) $resource['is_mx']) {
            return new ConnectionSettings($resource['apiKey'], $resource['url'], $resource['domain']);
        }
        return new ConnectionSettings($resource['apiKey'], '', '');
    }
}
