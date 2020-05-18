<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class NewsletterSettingsFactory
{
    public static function createFromArray(array $data): NewsletterSettings
    {
        if (empty($data)) {
            return new NewsletterSettings(0, '', null, '');
        }

        return new NewsletterSettings(
            (int) $data['status'],
            $data['campaignId'],
            $data['cycleDay'],
            $data['autoresponderId']
        );
    }
}
