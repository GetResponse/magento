<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class WebEventTrackingSettingsFactory
{
    public static function createFromArray(array $data): WebEventTrackingSettings
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
