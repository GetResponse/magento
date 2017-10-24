<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebEventTrackingFactory
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class WebEventTrackingSettingsFactory
{
    /**
     * @param array $data
     * @return WebEventTrackingSettings
     */
    public static function createFromArray(array $data)
    {
        if (empty($data)) {
            return new WebEventTrackingSettings(
                false,
                false,
                ''
            );
        }

        return new WebEventTrackingSettings(
            (bool)$data['isEnabled'],
            (bool)$data['isFeatureTrackingEnabled'],
            $data['codeSnippet']
        );
    }
}
