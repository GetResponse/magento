<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection as AutomationRulesCollection;
use GetresponseIntegration_Getresponse_Domain_AutomationRule as AutomationRule;

class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionFactory
{
    public static function createFromArray($data)
    {
        $rules = new AutomationRulesCollection;

        foreach ($data as $automationRule) {
            $rules->add(new AutomationRule(
                $automationRule['id'],
                $automationRule['categoryId'],
                $automationRule['campaignId'],
                $automationRule['action'],
                $automationRule['cycleDay'],
                $automationRule['active']
            ));
        }

        return $rules;
    }
}