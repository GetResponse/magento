<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection as AutomationRulesCollection;
use GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory as AutomationRuleFactory;

class GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionFactory
{
    /**
     * @param $data
     * @return GetresponseIntegration_Getresponse_Domain_AutomationRulesCollection
     */
    public static function createFromArray($data)
    {
        $rules = new AutomationRulesCollection();

        foreach ($data as $automationRule) {
            $rules->add(AutomationRuleFactory::createFromArray(array(
                'id' => $automationRule['id'],
                'categoryId' => $automationRule['categoryId'],
                'campaignId' => $automationRule['campaignId'],
                'action' => $automationRule['action'],
                'cycleDay' => $automationRule['cycleDay'],
                'active' => $automationRule['active']
            )));
        }

        return $rules;
    }
}