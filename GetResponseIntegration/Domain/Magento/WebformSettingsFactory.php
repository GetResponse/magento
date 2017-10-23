<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebformFactory
 */
class WebformSettingsFactory
{
    /**
     * @param $data
     *
     * @return WebformSettings
     */
    public static function buildFromUserPayload($data)
    {
        if (empty($data)) {
            return new WebformSettings(false, null, null, null);
        }

        return new WebformSettings(
            (isset($data['publish']) && 1 == $data['publish']) ? true : false,
            $data['webform_url'],
            $data['webform_id'],
            $data['sidebar']
        );
    }

    /**
     * @param array $resource
     *
     * @return WebformSettings
     */
    public static function buildFromRepository(array $resource)
    {
        if (empty($resource)) {
            return new WebformSettings(false, null, null, null);
        }

        return new WebformSettings(
            (bool) $resource['isEnabled'],
            $resource['url'],
            $resource['webformId'],
            $resource['sidebar']
        );
    }
}
