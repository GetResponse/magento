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
            $forJson[$key]['id'] = $field->getId();
            $forJson[$key]['customField'] = $field->getCustomField();
            $forJson[$key]['customValue'] = $field->getCustomValue();
            $forJson[$key]['default'] = $field->getIsDefault();
            $forJson[$key]['active'] = $field->getIsActive();
        }

        return $forJson;
    }
}