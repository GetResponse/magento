<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService;
use GrShareCode\GetresponseApiException;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class SubscribeFromNewsletter
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromNewsletter implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Session */
    private $session;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Session $session
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Session $session
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->session = $session;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @throws GetresponseApiException
     */
    public function execute(EventObserver $observer)
    {
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );

        if (!$newsletterSettings->isEnabled()) {
            return $this;
        }

        try {
            $grApiClient = $this->repositoryFactory->createGetResponseApiClient();

            /** @var Subscriber $subscriber */
            $subscriber = $observer->getEvent()->getSubscriber();

            if ($subscriber->getCustomerId() > 0) {
                return $this;
            }

            $email = $subscriber->getEmail();

            if (empty($email)) {
                return $this;
            }

            $service = new ContactService($grApiClient);
            $service->upsertContact(new AddContactCommand(
                $email,
                null,
                $newsletterSettings->getCampaignId(),
                $newsletterSettings->getCycleDay(),
                new ContactCustomFieldsCollection(),
                Config::ORIGIN_NAME
            ));
        } catch (RepositoryException $e) {
        } catch (ApiTypeException $e) {
        } finally {
            return $this;
        }
    }
}
