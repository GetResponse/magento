<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Helper\Message;

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
        $campaignId = isset($data['campaign']) ? $data['campaign'] : '';

        if (strlen($category) === 0) {
            return Message::SELECT_RULE_CATEGORY;
        }

        if (strlen($action) === 0) {
            return Message::SELECT_RULE_ACTION;
        }

        if (strlen($campaignId) === 0) {
            return Message::SELECT_RULE_TARGET_LIST;
        }

        return '';
    }
}
