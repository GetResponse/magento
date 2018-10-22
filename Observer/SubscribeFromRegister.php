<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\GetresponseApiException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
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

    /** @var ContactService */
    private $contactService;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param ContactService $contactService
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        ContactService $contactService
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->contactService = $contactService;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
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
                $this->contactService->createContact(
                    $customer->getEmail(),
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $registrationSettings->getCampaignId(),
                    $registrationSettings->getCycleDay(),
                    new ContactCustomFieldsCollection()
                );
            } catch (GetresponseApiException $e) {
            } catch (ConnectionSettingsException $e) {
            } catch (ApiTypeException $e) {
            }
        }

        return $this;
    }
}
