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
        if (empty($resource)) {
            return new WebformSettings(false, '', '', '');
        }
        return new WebformSettings(
            isset($resource['isEnabled']) ? $resource['isEnabled'] : 0,
            $resource['url'],
            $resource['webformId'],
            $resource['sidebar']
        );
    }
}
