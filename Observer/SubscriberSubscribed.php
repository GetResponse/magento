<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class SubscriberSubscribed
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscriberSubscribed implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /** @var ContactCustomFieldsCollectionFactory */
    private $contactCustomFieldsCollectionFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
     * @param ContactService $contactService
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        ContactService $contactService
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->contactService = $contactService;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if (!$observer->getEvent()->getSubscriber()->hasDataChanges()) {
            return $this;
        }
        
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );

        if (!$newsletterSettings->isEnabled()) {
            return $this;
        }

        try {

            /** @var Subscriber $subscriber */
            $subscriber = $observer->getEvent()->getSubscriber();

            // This is use case only for subscribers who are not customers
            if (0 !== $subscriber->getCustomerId()) {
                return $this;
            }

            $email = $subscriber->getEmail();

            if (empty($email)) {
                return $this;
            }

            $this->contactService->addContact(
                $email,
                '',
                '',
                $newsletterSettings->getCampaignId(),
                $newsletterSettings->getCycleDay(),
                $this->contactCustomFieldsCollectionFactory->createForSubscriber(),
                false
            );
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }
    }
}
