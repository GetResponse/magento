<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Command\AddContactCommand;
use GrShareCode\Contact\Command\FindContactCommand;
use GrShareCode\Contact\Command\UnsubscribeContactsCommand;
use GrShareCode\Contact\Contact;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;

/**
 * Class ContactService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactService
{
    /** @var ContactServiceFactory */
    private $contactServiceFactory;

    /**
     * @param ContactServiceFactory $contactServiceFactory
     */
    public function __construct(ContactServiceFactory $contactServiceFactory)
    {
        $this->contactServiceFactory = $contactServiceFactory;
    }

    /**
     * @param string $email
     * @param string $contactListId
     * @return null|Contact
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function findContactByEmail($email, $contactListId)
    {
        $contactService = $this->contactServiceFactory->create();

        return $contactService->findContact(
            new FindContactCommand($email, $contactListId, false)
        );
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $contactListId
     * @param int|null $dayOfCycle
     * @param ContactCustomFieldsCollection $customs
     * @param bool $updateIfAlreadyExists
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function addContact(
        $email,
        $firstName,
        $lastName,
        $contactListId,
        $dayOfCycle,
        ContactCustomFieldsCollection $customs,
        $updateIfAlreadyExists
    ) {
        $name = trim($firstName . ' ' . $lastName);

        $contactService = $this->contactServiceFactory->create();

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
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function removeContact($email)
    {
        $contactService = $this->contactServiceFactory->create();
        $contactService->unsubscribeContacts(
            new UnsubscribeContactsCommand($email)
        );
    }
}