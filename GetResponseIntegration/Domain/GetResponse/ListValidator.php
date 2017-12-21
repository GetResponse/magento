<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class ListValidator
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class ListValidator
{
    /**
     * @param array $data
     * @return string
     */
    public static function validateNewListParams(array $data)
    {
        if (!isset($data['campaign_name']) || strlen($data['campaign_name']) < 3) {
            return Message::LIST_VALIDATION_CAMPAIGN_NAME_ERROR;
        }

        if (!isset($data['from_field']) || strlen($data['from_field']) === 0) {
            return Message::LIST_VALIDATION_FROM_FIELD_ERROR;
        }

        if (!isset($data['reply_to_field']) || strlen($data['reply_to_field']) === 0) {
            return Message::LIST_VALIDATION_REPLY_TO_ERROR;
        }

        if (!isset($data['confirmation_subject']) || strlen($data['confirmation_subject']) === 0) {
            return Message::LIST_VALIDATION_CONFIRMATION_SUBJECT_ERROR;
        }

        if (!isset($data['confirmation_body']) || strlen($data['confirmation_body']) === 0) {
            return Message::LIST_VALIDATION_CONFIRMATION_BODY;
        }

        return '';
    }
}
