<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRule as AutomationRule;

class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection
{
    private $rules;

    public function add(AutomationRule $rule)
    {
        foreach ($this->rules as $data){
            if ($data->getCategoryId() === $rule->getCategoryId()) {
                return false;
            }
        }
        $this->rules[] = $rule;

        return true;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function toArray()
    {
        $forJson = [];
        foreach ($this->rules as $key => $rule) {
            $forJson[$key]['id'] = $rule->getId();
            $forJson[$key]['categoryId'] = $rule->getCategoryId();
            $forJson[$key]['campaignId'] = $rule->getCampaignId();
            $forJson[$key]['action'] = $rule->getAction();
            $forJson[$key]['cycleDay'] = $rule->getCycleDay();
            $forJson[$key]['active'] = $rule->isActive();
        }

        return $forJson;
    }
}