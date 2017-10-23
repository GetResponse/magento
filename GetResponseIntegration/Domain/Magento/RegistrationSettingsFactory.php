<?php

namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class RegistrationSettingsFactory
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class RegistrationSettingsFactory
{
    /**
     * @param array $data
     *
     * @return RegistrationSettings
     */
    public static function createFromArray(array $data)
    {
        if (empty($data)) {
            return new RegistrationSettings(0, 0, '', 0);
        }

        return new RegistrationSettings(
            $data['status'],
            $data['customFieldsStatus'],
            $data['campaignId'],
            $data['cycleDay']
        );
    }
}
