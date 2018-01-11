<?php

class GetresponseIntegration_Getresponse_Domain_Shop
{
    private $grShopId;
    private $isEnabled;

    /**
     * GetresponseIntegration_Getresponse_Domain_Shop constructor.
     * @param $shopId
     * @param $grShopId
     * @param $isEnabled
     */
    public function __construct($grShopId, $isEnabled)
    {
        $this->grShopId = $grShopId;
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return mixed
     */
    public function getGrShopId()
    {
        return $this->grShopId;
    }

    /**
     * @return mixed
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'grShopId' => $this->grShopId,
            'isEnabled' => $this->isEnabled
        ];
    }
}