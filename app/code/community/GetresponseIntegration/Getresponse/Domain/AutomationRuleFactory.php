<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRule as AutomationRule;

/**
 * Class GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory
 */
class GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory
{
    /**
     * @param array $data
     * @return GetresponseIntegration_Getresponse_Domain_AutomationRule
     */
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
