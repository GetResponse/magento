<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\RemoveContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\Query\SubscriberEmail;
use GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\SubscriberReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class SubscriberOrCustomerUnsubscribed implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $subscribeViaRegistrationService;
    private $magentoStore;
    private $subscriberReadModel;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        MagentoStore $magentoStore,
        SubscriberReadModel $subscriberReadModel
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->magentoStore = $magentoStore;
        $this->subscriberReadModel = $subscriberReadModel;
    }
    public function execute(Observer $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();
        $subscriber = $observer->getEvent()->getSubscriber();

        try {
            $registrationSettings = $this->subscribeViaRegistrationService->getSettings($scope);

            $newsletterSettings = NewsletterSettingsFactory::createFromArray(
                $this->repository->getNewsletterSettings($scope->getScopeId())
            );

            if (!$registrationSettings->isEnabled() && !$newsletterSettings->isEnabled()) {
                return $this;
            }
            
            $subscriber = $this->subscriberReadModel->loadSubscriberByEmail(
                new SubscriberEmail($subscriber->getSubscriberEmail())
            );

            if ($subscriber->getStatus() === Subscriber::STATUS_UNSUBSCRIBED) {
                $this->contactService->removeContact(
                    new RemoveContact($scope, $subscriber->getSubscriberEmail())
                );
            }
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }

        return $this;
    }
}
