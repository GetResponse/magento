<?php
/**
 * Created by PhpStorm.
 * User: mjaniszewski
 * Date: 11/12/2017
 * Time: 17:41
 */

class GetresponseIntegration_Getresponse_Domain_Webform
{
    private $webformId;
    private $activeSubscription;
    private $layoutPosition;
    private $blockPosition;
    private $webformTitle;
    private $url;

    /**
     * GetresponseIntegration_Getresponse_Domain_Webform constructor.
     * @param $webformId
     * @param $activeSubscription
     * @param $layoutPosition
     * @param $blockPosition
     * @param $webformTitle
     * @param $url
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
     * @return mixed
     */
    public function getWebformId()
    {
        return $this->webformId;
    }

    /**
     * @return mixed
     */
    public function isActiveSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * @return mixed
     */
    public function getLayoutPosition()
    {
        return $this->layoutPosition;
    }

    /**
     * @return mixed
     */
    public function getBlockPosition()
    {
        return $this->blockPosition;
    }

    /**
     * @return mixed
     */
    public function getWebformTitle()
    {
        return $this->webformTitle;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function toArray()
    {
        return [
            'webformId' => $this->webformId,
            'activeSubscription' => $this->activeSubscription,
            'layoutPosition' => $this->layoutPosition,
            'blockPosition' => $this->blockPosition,
            'webformTitle' => $this->webformTitle,
            'url' => $this->url
        ];
    }
}