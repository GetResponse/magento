<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRule as AutomationRule;

class GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory
{
    public static function createFromArray($data)
    {
        return new AutomationRule(
            $data['id'],
            $data['categoryId'],
            $data['campaignId'],
            $data['action'],
            $data['cycleDay'],
            $data['active']
        );
    }
}