<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebformFactory
 */
class WebformSettingsFactory
{
    /**
     * @param array $resource
     *
     * @return WebformSettings
     */
    public static function createFromArray(array $resource)
    {
        return new WebformSettings(
            (bool)$resource['isEnabled'],
            $resource['url'],
            $resource['webformId'],
            $resource['sidebar']
        );
    }
}
