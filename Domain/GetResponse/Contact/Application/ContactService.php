<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\RemoveContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Command\AddContactCommand;
use GrShareCode\Contact\Command\UnsubscribeContactsCommand;

class ContactService
{
    private $contactServiceFactory;

    public function __construct(ContactServiceFactory $contactServiceFactory)
    {
        $this->contactServiceFactory = $contactServiceFactory;
    }

    /**
     * @param AddContact $command
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function addContact(AddContact $command)
    {
        $name = trim($command->getFirstName() . ' ' . $command->getLastName());

        $contactService = $this->contactServiceFactory->create($command->getScope());

        $contactService->addContact(
            new AddContactCommand(
                $command->getEmail(),
                $name,
                $command->getContactListId(),
                $command->getDayOfCycle(),
                $command->getCustoms(),
                $command->isUpdateIfAlreadyExists()
            )
        );
    }

    /**
     * @param RemoveContact $command
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function removeContact(RemoveContact $command)
    {
        $contactService = $this->contactServiceFactory->create($command->getScope());
        $contactService->unsubscribeContacts(
            new UnsubscribeContactsCommand($command->getEmail())
        );
    }
}
