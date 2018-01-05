<?php
use GetresponseIntegration_Getresponse_Domain_Settings as Settings;

class GetresponseIntegration_Getresponse_Domain_SettingsFactory
{
    public static function createFromArray($data = [])
    {
        return new Settings(
            isset($data['apiKey']) ? $data['apiKey'] : '',
            isset($data['apiUrl']) ? $data['apiUrl'] : '',
            isset($data['apiDomain']) ? $data['apiDomain'] : '',
            isset($data['activeSubscription']) ? $data['activeSubscription'] : '',
            isset($data['updateAddress']) ? $data['updateAddress'] : '',
            isset($data['campaignId']) ? $data['campaignId'] : null,
            isset($data['cycleDay']) ? $data['cycleDay'] : null,
            isset($data['subscriptionOnCheckout']) ? $data['subscriptionOnCheckout'] : '',
            isset($data['hasGrTrafficFeatureEnabled']) ? $data['hasGrTrafficFeatureEnabled'] : '',
            isset($data['hasActiveTrafficModule']) ? $data['hasActiveTrafficModule'] : '',
            isset($data['trackingCodeSnippet']) ? $data['trackingCodeSnippet'] : '',
            isset($data['newsletterSubscription']) ? $data['newsletterSubscription'] : '',
            isset($data['newsletterCampaignId']) ? $data['newsletterCampaignId'] : null,
            isset($data['newsletterCycleDay']) ? $data['newsletterCycleDay'] : null
        );
    }
}