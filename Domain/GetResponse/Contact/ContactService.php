<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Command\AddContactCommand;
use GrShareCode\Contact\Command\UnsubscribeContactsCommand;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;

class ContactService
{
    private $contactServiceFactory;

    public function __construct(ContactServiceFactory $contactServiceFactory)
    {
        $this->contactServiceFactory = $contactServiceFactory;
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $contactListId
     * @param int|null $dayOfCycle
     * @param ContactCustomFieldsCollection $customs
     * @param bool $updateIfAlreadyExists
     * @param Scope $scope
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function addContact(
        $email,
        $firstName,
        $lastName,
        $contactListId,
        $dayOfCycle,
        ContactCustomFieldsCollection $customs,
        $updateIfAlreadyExists,
        Scope $scope
    ) {
        $name = trim($firstName . ' ' . $lastName);

        $contactService = $this->contactServiceFactory->create($scope);

        $contactService->addContact(
            new AddContactCommand(
                $email,
                $name,
                $contactListId,
                $dayOfCycle,
                $customs,
                $updateIfAlreadyExists
            )
        );
    }

    /**
     * @param string $email
     * @param Scope $scope
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function removeContact($email, Scope $scope)
    {
        $contactService = $this->contactServiceFactory->create($scope);
        $contactService->unsubscribeContacts(
            new UnsubscribeContactsCommand($email)
        );
    }
}