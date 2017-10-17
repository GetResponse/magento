<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

/**
 * Class RuleValidator
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class RuleValidator
{
    /**
     * @param array $data
     *
     * @return string
     */
    public static function validateForPostedParams($data)
    {
        $category = isset($data['category']) ? $data['category'] : '';
        $action = isset($data['action']) ? $data['action'] : '';
        $campaignId = isset($data['campaign_id']) ? $data['campaign_id'] : '';

        if (strlen($category) === 0) {
            return 'You need to select your product category';
        }

        if (strlen($action) === 0) {
            return 'You need to select what to do with the customer';
        }

        if (strlen($campaignId) === 0) {
            return 'You need to select a target list';
        }

        return '';
    }
}
