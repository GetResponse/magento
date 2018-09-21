<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\Contact;
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
     * @throws ContactNotFoundException
     * @throws GetresponseApiException
     * @throws ApiTypeException
     */
    public function getContactByEmail($email, $contactListId)
    {
        $contactService = $this->contactServiceFactory->create();
        return $contactService->getContactByEmail($email, $contactListId);
    }
}