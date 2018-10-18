<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\Contact;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactNotFoundException;
use GrShareCode\GetresponseApiException;

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
     * @return Contact
     * @throws ApiTypeException
     * @throws ContactNotFoundException
     * @throws GetresponseApiException
     * @throws ConnectionSettingsException
     */
    public function getContactByEmail($email, $contactListId)
    {
        $contactService = $this->contactServiceFactory->create();
        return $contactService->getContactByEmail($email, $contactListId);
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $campaignId
     * @param int|null $dayOfCycle
     * @param ContactCustomFieldsCollection $customs
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function createContact($email, $firstName, $lastName, $campaignId, $dayOfCycle, ContactCustomFieldsCollection $customs)
    {
        $name = trim($firstName . ' ' . $lastName);

        if (!is_int($dayOfCycle)) {
            $dayOfCycle = null;
        }

        $contactService = $this->contactServiceFactory->create();
        $contactService->createContact(new AddContactCommand(
            $email,
            $name,
            $campaignId,
            $dayOfCycle,
            $customs,
            Config::ORIGIN_NAME
        ));
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $campaignId
     * @param int|null $dayOfCycle
     * @param ContactCustomFieldsCollection $customs
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     * @throws GetresponseApiException
     */
    public function upsertContact($email, $firstName, $lastName, $campaignId, $dayOfCycle, ContactCustomFieldsCollection $customs)
    {
        $name = trim($firstName . ' ' . $lastName);

        if (!is_int($dayOfCycle)) {
            $dayOfCycle = null;
        }

        $contactService = $this->contactServiceFactory->create();
        $contactService->upsertContact(new AddContactCommand(
            $email,
            $name,
            $campaignId,
            $dayOfCycle,
            $customs,
            Config::ORIGIN_NAME
        ));
    }
}