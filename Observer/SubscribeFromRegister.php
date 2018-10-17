<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService;
use GrShareCode\GetresponseApiException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromRegister
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromRegister implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $registrationSettings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {

            try {
                $grApiClient = $this->repositoryFactory->createGetResponseApiClient();
                $service = new ContactService($grApiClient);
                $service->createContact(new AddContactCommand(
                    $customer->getEmail(),
                    $customer->getFirstname() . ' ' . $customer->getLastname(),
                    $registrationSettings->getCampaignId(),
                    $registrationSettings->getCycleDay(),
                    new ContactCustomFieldsCollection(),
                    Config::ORIGIN_NAME
                ));
            } catch (RepositoryException $e) {
            } catch (GetresponseApiException $e) {}
        }

        return $this;
    }
}
