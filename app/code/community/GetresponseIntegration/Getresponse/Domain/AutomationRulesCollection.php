<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRule as AutomationRule;

class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection
{
    /** @var array */
    private $rules = [];

    /**
     * @param GetresponseIntegration_Getresponse_Domain_AutomationRule $rule
     * @return bool
     */
    public function add(AutomationRule $rule)
    {
        foreach ($this->rules as $data){
            $this->checkIfRuleForCampaignIdAlreadyExists($data, $rule);
        }
        $this->rules[] = $rule;

        return true;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $forJson = [];
        foreach ($this->rules as $key => $rule) {
            $forJson[$key] = $rule->toArray();
        }

        return $forJson;
    }

    /**
     * @param $categoryId
     * @param $addedCategory
     * @return bool
     */
    public function checkIfRuleForCampaignIdAlreadyExists($categoryId, $addedCategory)
    {
        if ($categoryId->getCategoryId() === $addedCategory->getCategoryId()) {
            return false;
        }
    }
}