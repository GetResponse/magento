<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\Contact;
use GrShareCode\Contact\ContactNotFoundException;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\GetresponseApiException;

/**
 * Class ContactService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactService
{
    /** @var GrContactService */
    private $grContactService;

    /**
     * @param ContactServiceFactory $contactServiceFactory
     * @throws ApiTypeException
     */
    public function __construct(ContactServiceFactory $contactServiceFactory)
    {
        $this->grContactService = $contactServiceFactory->create();
    }

    /**
     * @param string $email
     * @param string $contactListId
     * @return Contact
     * @throws ContactNotFoundException
     * @throws GetresponseApiException
     */
    public function getContactByEmail($email, $contactListId)
    {
        return $this->grContactService->getContactByEmail($email, $contactListId);
    }
}