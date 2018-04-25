<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Webform
 */
class GetresponseIntegration_Getresponse_Domain_Webform
{
    /** @var string */
    private $webformId;

    /** @var string */
    private $activeSubscription;

    /** @var string */
    private $layoutPosition;

    /** @var string */
    private $blockPosition;

    /** @var string */
    private $webformTitle;

    /** @var string */
    private $url;

    /**
     * @param string $webformId
     * @param string $activeSubscription
     * @param string $layoutPosition
     * @param string $blockPosition
     * @param string $webformTitle
     * @param string $url
     */
    public function __construct($webformId, $activeSubscription, $layoutPosition, $blockPosition, $webformTitle, $url)
    {
        $this->webformId = $webformId;
        $this->activeSubscription = $activeSubscription;
        $this->layoutPosition = $layoutPosition;
        $this->blockPosition = $blockPosition;
        $this->webformTitle = $webformTitle;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getWebformId()
    {
        return $this->webformId;
    }

    /**
     * @return string
     */
    public function isActiveSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * @return string
     */
    public function getLayoutPosition()
    {
        return $this->layoutPosition;
    }

    /**
     * @return string
     */
    public function getBlockPosition()
    {
        return $this->blockPosition;
    }

    /**
     * @return string
     */
    public function getWebformTitle()
    {
        return $this->webformTitle;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'webformId' => $this->webformId,
            'activeSubscription' => $this->activeSubscription,
            'layoutPosition' => $this->layoutPosition,
            'blockPosition' => $this->blockPosition,
            'webformTitle' => $this->webformTitle,
            'url' => $this->url
        );
    }
}
