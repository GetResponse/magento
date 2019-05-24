<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscriberUnsubscribed
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscriberOrCustomerUnsubscribed implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /** @var SubscribeViaRegistrationService */
    private $subscribeViaRegistrationService;

    /** @var ContactCustomFieldsCollectionFactory */
    private $contactCustomFieldsCollectionFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param ContactService $contactService
     * @param SubscribeViaRegistrationService $subscribeViaRegistrationService
     * @param ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
    }
    public function execute(Observer $observer)
    {
        try {
            
            if (!$observer->getEvent()->getSubscriber()->hasDataChanges()) {
                return $this;
            }
            
            $registrationSettings = $this->subscribeViaRegistrationService->getSettings();
            $newsletterSettings = NewsletterSettingsFactory::createFromArray($this->repository->getNewsletterSettings());

            if (!$registrationSettings->isEnabled() && !$newsletterSettings->isEnabled()) {
                return $this;
            }
            
            $subscriber = $this->repository->loadSubscriberByEmail($observer->getEvent()->getSubscriber()->getSubscriberEmail());

            if (!$subscriber->isSubscribed()) {
                $this->contactService->removeContact($subscriber->getSubscriberEmail());
            }
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }

        return $this;
    }
}
