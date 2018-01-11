<?php
/**
 * Created by PhpStorm.
 * User: mjaniszewski
 * Date: 11/12/2017
 * Time: 14:47
 */

class GetresponseIntegration_Getresponse_Domain_Settings
{
    private $apiKey;
    private $apiUrl;
    private $apiDomain;
    private $activeSubscription;
    private $updateAddress;
    private $campaignId;
    private $cycleDay;
    private $subscriptionOnCheckout;
    private $hasGrTrafficFeatureEnabled;
    private $hasActiveTrafficModule;
    private $trackingCodeSnippet;
    private $newsletterSubscription;
    private $newsletterCampaignId;
    private $newsletterCycleDay;

    /**
     * GetresponseIntegration_Getresponse_Domain_Settings constructor.
     * @param $apiKey
     * @param $apiUrl
     * @param $apiDomain
     * @param $activeSubscription
     * @param $updateAddress
     * @param $campaignId
     * @param $cycleDay
     * @param $subscriptionOnCheckout
     * @param $hasGrTrafficFeatureEnabled
     * @param $hasActiveTrafficModule
     * @param $trackingCodeSnippet
     * @param $newsletterSubscription
     * @param $newsletterCampaignId
     * @param $newsletterCycleDay
     */
    public function __construct(
        $apiKey,
        $apiUrl,
        $apiDomain,
        $activeSubscription,
        $updateAddress,
        $campaignId,
        $cycleDay,
        $subscriptionOnCheckout,
        $hasGrTrafficFeatureEnabled,
        $hasActiveTrafficModule,
        $trackingCodeSnippet,
        $newsletterSubscription,
        $newsletterCampaignId,
        $newsletterCycleDay
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->apiDomain = $apiDomain;
        $this->activeSubscription = $activeSubscription;
        $this->updateAddress = $updateAddress;
        $this->campaignId = $campaignId;
        $this->cycleDay = $cycleDay;
        $this->subscriptionOnCheckout = $subscriptionOnCheckout;
        $this->hasGrTrafficFeatureEnabled = $hasGrTrafficFeatureEnabled;
        $this->hasActiveTrafficModule = $hasActiveTrafficModule;
        $this->trackingCodeSnippet = $trackingCodeSnippet;
        $this->newsletterSubscription = $newsletterSubscription;
        $this->newsletterCampaignId = $newsletterCampaignId;
        $this->newsletterCycleDay = $newsletterCycleDay;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @return mixed
     */
    public function getApiDomain()
    {
        return $this->apiDomain;
    }

    /**
     * @return mixed
     */
    public function getActiveSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * @return mixed
     */
    public function getUpdateAddress()
    {
        return $this->updateAddress;
    }

    /**
     * @return mixed
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return mixed
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionOnCheckout()
    {
        return $this->subscriptionOnCheckout;
    }

    /**
     * @return mixed
     */
    public function getHasGrTrafficFeatureEnabled()
    {
        return $this->hasGrTrafficFeatureEnabled;
    }

    /**
     * @return mixed
     */
    public function getHasActiveTrafficModule()
    {
        return $this->hasActiveTrafficModule;
    }

    /**
     * @return mixed
     */
    public function getTrackingCodeSnippet()
    {
        return $this->trackingCodeSnippet;
    }

    /**
     * @return mixed
     */
    public function getNewsletterSubscription()
    {
        return $this->newsletterSubscription;
    }

    /**
     * @return mixed
     */
    public function getNewsletterCampaignId()
    {
        return $this->newsletterCampaignId;
    }

    /**
     * @return mixed
     */
    public function getNewsletterCycleDay()
    {
        return $this->newsletterCycleDay;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'apiKey' => $this->apiKey,
            'apiUrl' => $this->apiUrl,
            'apiDomain' => $this->apiDomain,
            'activeSubscription' => $this->activeSubscription,
            'updateAddress' => $this->updateAddress,
            'campaignId' => $this->campaignId,
            'cycleDay' => $this->cycleDay,
            'subscriptionOnCheckout' => $this->subscriptionOnCheckout,
            'hasGrTrafficFeatureEnabled' => $this->hasGrTrafficFeatureEnabled,
            'hasActiveTrafficModule' => $this->hasActiveTrafficModule,
            'trackingCodeSnippet' => $this->trackingCodeSnippet,
            'newsletterSubscription' => $this->newsletterSubscription,
            'newsletterCampaignId' => $this->newsletterCampaignId,
            'newsletterCycleDay' => $this->newsletterCycleDay
        ];
    }
}