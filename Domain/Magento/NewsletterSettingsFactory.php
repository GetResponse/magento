<?php

namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class NewsletterSettingsFactory
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class NewsletterSettingsFactory
{
    /**
     * @param array $data
     *
     * @return NewsletterSettings
     */
    public static function createFromArray(array $data)
    {
        if (empty($data)) {
            return new NewsletterSettings(0, '', 0);
        }

        return new NewsletterSettings(
            $data['status'],
            $data['campaignId'],
            $data['cycleDay']
        );
    }
}
