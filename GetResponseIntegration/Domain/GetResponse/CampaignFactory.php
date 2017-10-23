<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CampaignFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CampaignFactory
{
    /**
     * @param array $data
     * @param string $language
     * @return Campaign
     */
    public static function createFromArray(array $data, $language = 'EN')
    {
        return new Campaign(
            null,
            $data['campaign_name'],
            $data['from_field'],
            $data['reply_to_field'],
            $data['confirmation_subject'],
            $data['confirmation_body'],
            $language
        );
    }
}
