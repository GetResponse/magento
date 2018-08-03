<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GrShareCode\Contact\Contact;
use GrShareCode\Contact\ContactNotFoundException;
use GrShareCode\GetresponseApiException;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Observer
 */
class Ecommerce
{
    /** @var Session */
    protected $customerSession;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var CountryFactory */
    protected $countryFactory;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param Repository $repository
     * @param ContactService $contactService
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        Repository $repository,
        ContactService $contactService
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->repository = $repository;
        $this->contactService = $contactService;
    }

    /**
     * @return bool
     * @throws GetresponseApiException
     */
    protected function canHandleECommerceEvent()
    {
        if (false === $this->customerSession->isLoggedIn()) {
            return false;
        }

        return null !== $this->getContactFromGetResponse();
    }

    /**
     * @return null|Contact
     * @throws GetresponseApiException
     */
    private function getContactFromGetResponse()
    {
        $cache = $this->objectManager->get(CacheInterface::class);

        $settings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );
        $contactListId = $settings->getCampaignId();
        $contactEmail = $this->customerSession->getCustomer()->getEmail();

        $cacheKey = md5($contactEmail . $contactListId);
        $cachedCustomer = $cache->load($cacheKey);

        if (false !== $cachedCustomer) {
            return unserialize($cachedCustomer);
        }

        try {
            $contact = $this->contactService->getContactByEmail($contactEmail, $contactListId);
        } catch (ContactNotFoundException $e) {
            $contact = null;
        }

        $cache->save(serialize($contact), $cacheKey, [Config::CACHE_KEY], Config::CACHE_TIME);

        return $contact;
    }

}
