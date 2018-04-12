<?php
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;

/**
 * Class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection
 */
class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection
{
    /** @var array  */
    private $fields = array();

    /**
     * @param GetresponseIntegration_Getresponse_Domain_CustomField $field
     * @return bool
     */
    public function add(CustomField $field)
    {
        foreach ($this->fields as $data){
            $this->checkIfCustomFieldExists($data, $field);
        }
        $this->fields[] = $field;

        return true;
    }

    /**
     * @return mixed
     */
    public function getCustomFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $forJson = array();
        foreach ($this->fields as $key => $field) {
            $forJson[$key] = $field->toArray();
        }

        return $forJson;
    }

    /**
     * @param $data
     * @param $field
     * @return bool
     */
    private function checkIfCustomFieldExists($data, $field)
    {
        if ($data->getCustomField() === $field->getCustomField()) {
            return false;
        }
    }
}
