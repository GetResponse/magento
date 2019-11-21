<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Settings
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
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $apiDomain
     * @param string $activeSubscription
     * @param string $updateAddress
     * @param string $campaignId
     * @param int $cycleDay
     * @param string $subscriptionOnCheckout
     * @param bool $hasGrTrafficFeatureEnabled
     * @param bool $hasActiveTrafficModule
     * @param string $trackingCodeSnippet
     * @param string $newsletterSubscription
     * @param string $newsletterCampaignId
     * @param string $newsletterCycleDay
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
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getApiDomain()
    {
        return $this->apiDomain;
    }

    /**
     * @return string
     */
    public function getActiveSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * @return string
     */
    public function getUpdateAddress()
    {
        return $this->updateAddress;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return string
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }

    /**
     * @return string
     */
    public function getSubscriptionOnCheckout()
    {
        return $this->subscriptionOnCheckout;
    }

    /**
     * @return bool
     */
    public function getHasGrTrafficFeatureEnabled()
    {
        return $this->hasGrTrafficFeatureEnabled;
    }

    /**
     * @return bool
     */
    public function getHasActiveTrafficModule()
    {
        return $this->hasActiveTrafficModule;
    }

    /**
     * @return string
     */
    public function getTrackingCodeSnippet()
    {
        return $this->trackingCodeSnippet;
    }

    /**
     * @return string
     */
    public function getNewsletterSubscription()
    {
        return $this->newsletterSubscription;
    }

    /**
     * @return string
     */
    public function getNewsletterCampaignId()
    {
        return $this->newsletterCampaignId;
    }

    /**
     * @return string
     */
    public function getNewsletterCycleDay()
    {
        return $this->newsletterCycleDay;
    }

    public function hasApiKey()
    {
        return !empty($this->getApiKey());
    }

    public function isTurnOnAddContactAfterCustomerRegister()
    {
        return 1 === (int)$this->getActiveSubscription() && !empty($this->getCampaignId());
    }

    public function isTurnOnAddContactAfterNewsletterSubscription()
    {
        return 1 === (int)$this->getNewsletterSubscription() && !empty($this->getNewsletterCampaignId());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
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
        );
    }
}