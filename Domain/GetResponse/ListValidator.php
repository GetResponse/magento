<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\Message;

class ListValidator
{
    public static function validateNewListParams(array $data): string
    {
        if (strlen($data['campaign_name']) < 3) {
            return Message::LIST_VALIDATION_CAMPAIGN_NAME_ERROR;
        }

        if ($data['from_field'] === '') {
            return Message::LIST_VALIDATION_FROM_FIELD_ERROR;
        }

        if ($data['reply_to_field'] === '') {
            return Message::LIST_VALIDATION_REPLY_TO_ERROR;
        }

        if ($data['confirmation_subject'] === '') {
            return Message::LIST_VALIDATION_CONFIRMATION_SUBJECT_ERROR;
        }

        if ($data['confirmation_body'] === '') {
            return Message::LIST_VALIDATION_CONFIRMATION_BODY;
        }

        return '';
    }
}
