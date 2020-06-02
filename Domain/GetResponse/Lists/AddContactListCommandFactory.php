<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Lists;

use GrShareCode\ContactList\Command\AddContactListCommand;

class AddContactListCommandFactory
{
    public static function createFromArray(array $data): AddContactListCommand
    {
        return new AddContactListCommand(
            $data['campaign_name'],
            $data['from_field'],
            $data['reply_to_field'],
            $data['confirmation_body'],
            $data['confirmation_subject'],
            $data['lang']
        );
    }
}
