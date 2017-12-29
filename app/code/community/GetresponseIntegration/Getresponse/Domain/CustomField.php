<?php
/**
 * Created by PhpStorm.
 * User: mjaniszewski
 * Date: 12/12/2017
 * Time: 10:06
 */

class GetresponseIntegration_Getresponse_Domain_CustomField
{
    private $id;
    private $customField;
    private $customValue;
    private $isDefault;
    private $isActive;

    /**
     * GetresponseIntegration_Getresponse_Domain_CustomField constructor.
     * @param $id
     * @param $customField
     * @param $customValue
     * @param $isDefault
     * @param $activeCustom
     */
    public function __construct($id, $customField, $customValue, $isDefault, $activeCustom)
    {
        $this->id = $id;
        $this->customField = $customField;
        $this->customValue = $customValue;
        $this->isDefault = $isDefault;
        $this->isActive = $activeCustom;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCustomField()
    {
        return $this->customField;
    }

    /**
     * @return mixed
     */
    public function getCustomValue()
    {
        return $this->customValue;
    }

    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    public function toArray()
    {
        return [
            'id'    => $this->id,
            'customField'        => $this->customField,
            'customValue'        => $this->customValue,
            'isDefault'      => $this->isDefault,
            'active'        => $this->isActive
        ];
    }
}