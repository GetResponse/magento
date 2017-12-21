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
        if (strlen($data['campaign_name']) < 3) {
            return Message::LIST_VALIDATION_CAMPAIGN_NAME_ERROR;
        }

        if (strlen($data['from_field']) === 0) {
            return Message::LIST_VALIDATION_FROM_FIELD_ERROR;
        }

        if (strlen($data['reply_to_field']) === 0) {
            return Message::LIST_VALIDATION_REPLY_TO_ERROR;
        }

        if (strlen($data['confirmation_subject']) === 0) {
            return Message::LIST_VALIDATION_CONFIRMATION_SUBJECT_ERROR;
        }

        if (strlen($data['confirmation_body']) === 0) {
            return Message::LIST_VALIDATION_CONFIRMATION_BODY;
        }

        return '';
    }
}
