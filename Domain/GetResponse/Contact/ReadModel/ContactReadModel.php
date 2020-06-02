<?php
declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Helper\Config;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Command\FindContactCommand;
use GrShareCode\Contact\Contact;
use Magento\Framework\App\CacheInterface;

class ContactReadModel
{
    private $cache;
    private $contactServiceFactory;

    public function __construct(
        ContactServiceFactory $contactServiceFactory,
        CacheInterface $cache
    ) {
        $this->contactServiceFactory = $contactServiceFactory;
        $this->cache = $cache;
    }

    /**
     * @param ContactByEmail $query
     * @return null|Contact
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function findContactByEmail(ContactByEmail $query)
    {
        $cacheKey = md5($query->getEmail() . $query->getContactListId());
        $cachedCustomer = $this->cache->load($cacheKey);

        if (false !== $cachedCustomer) {
            return unserialize($cachedCustomer, ['allowed_classes' => [Contact::class]]);
        }

        $contactService = $this->contactServiceFactory->create($query->getScope());

        $contact = $contactService->findContact(
            new FindContactCommand(
                $query->getEmail(), $query->getContactListId(), false)
        );

        if (!$contact) {
            return null;
        }

        $this->cache->save(serialize($contact), $cacheKey, [Config::CACHE_KEY], Config::CACHE_TIME);

        return $contact;
    }
}
