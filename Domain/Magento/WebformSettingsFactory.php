<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class WebformSettingsFactory
{
    public static function createFromArray(array $resource): WebformSettings
    {
        if (empty($resource)) {
            return new WebformSettings(false, '', '', '');
        }
        return new WebformSettings(
            isset($resource['isEnabled']) ? (bool) $resource['isEnabled'] : 0,
            $resource['url'],
            $resource['webformId'],
            $resource['sidebar']
        );
    }
}
