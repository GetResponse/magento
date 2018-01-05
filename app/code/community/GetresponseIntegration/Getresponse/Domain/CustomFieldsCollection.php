<?php
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;

class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection
{
    private $fields;

    public function add(CustomField $field)
    {
        foreach ($this->fields as $data){
            if ($data->getCustomField() === $field->getCustomField()) {
                return false;
            }
        }
        $this->fields[] = $field;

        return true;
    }

    public function getCustomFields()
    {
        return $this->fields;
    }

    public function toArray()
    {
        $forJson = [];
        foreach ($this->fields as $key => $field) {
            $forJson[$key]['id_custom'] = $field->getId();
            $forJson[$key]['custom_field'] = $field->getCustomField();
            $forJson[$key]['custom_value'] = $field->getCustomValue();
            $forJson[$key]['default'] = $field->getIsDefault();
            $forJson[$key]['custom_active'] = $field->getIsActive();
        }

        return $forJson;
    }
}