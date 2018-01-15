<?php

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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id_custom' => $this->id,
            'custom_field' => $this->customField,
            'custom_value' => $this->customValue,
            'default' => $this->isDefault,
            'custom_active' => $this->isActive
        ];
    }
}