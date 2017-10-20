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
    public static function createFromRepository(array $data)
    {
        if (empty($data)) {
            return new RegistrationSettings(null, null, null, null);
        }

        return new RegistrationSettings(
            $data['status'],
            $data['customFieldsStatus'],
            $data['campaignId'],
            $data['cycleDay']
        );
    }

    /**
     * @param int $status
     * @param int $customFieldStatus
     * @param string $campaignId
     * @param int $cycleDay
     *
     * @return RegistrationSettings
     */
    public static function buildFromPayload($status, $customFieldStatus, $campaignId, $cycleDay)
    {
        return new RegistrationSettings(
            $status,
            $customFieldStatus,
            $campaignId,
            $cycleDay
        );
    }
}