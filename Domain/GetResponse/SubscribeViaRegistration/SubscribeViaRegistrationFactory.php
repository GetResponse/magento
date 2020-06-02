<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration;

class SubscribeViaRegistrationFactory
{
    public static function createFromArray(array $data): SubscribeViaRegistration
    {
        if (empty($data)) {
            return new SubscribeViaRegistration(0, 0, '', null, '');
        }

        return new SubscribeViaRegistration(
            (int)$data['status'],
            (int)$data['customFieldsStatus'],
            $data['campaignId'],
            $data['cycleDay'],
            isset($data['autoresponderId']) ? $data['autoresponderId'] : ''
        );
    }
}
