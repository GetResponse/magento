<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebEventTrackingFactory
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class WebEventTrackingFactory
{
    /**
     * @param int $isEnabled
     * @param int $isFeatureTrackingEnabled
     * @param string $codeSnippet
     * @return WebEventTracking
     */
    public static function buildFromParams($isEnabled, $isFeatureTrackingEnabled, $codeSnippet)
    {
        return new WebEventTracking((bool) $isEnabled, (bool) $isFeatureTrackingEnabled, $codeSnippet);
    }

    /**
     * @param array $data
     * @return WebEventTracking
     */
    public static function buildFromRepository(array $data)
    {
        return new WebEventTracking(
            (bool) $data['isEnabled'],
            (bool) $data['isFeatureTrackingEnabled'],
            $data['codeSnippet']
        );
    }
}
