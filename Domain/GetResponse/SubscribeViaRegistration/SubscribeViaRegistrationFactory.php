<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration;

/**
 * Class SubscribeViaRegistrationFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration
 */
class SubscribeViaRegistrationFactory
{
    /**
     * @param array $data
     * @return SubscribeViaRegistration
     */
    public static function createFromArray(array $data)
    {
        if (empty($data)) {
            return new SubscribeViaRegistration(0, 0, '', 0, '');
        }

        return new SubscribeViaRegistration(
            $data['status'],
            $data['customFieldsStatus'],
            $data['campaignId'],
            $data['cycleDay'],
            $data['autoresponderId']
        );
    }
}
