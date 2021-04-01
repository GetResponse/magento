<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\RemoveContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
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
    private $logger;
    private $apiService;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        MagentoStore $magentoStore,
        Logger $logger,
        ApiService $apiService
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->magentoStore = $magentoStore;
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    public function execute(Observer $observer): SubscriberOrCustomerUnsubscribed
    {
        try {
            $scope = $this->magentoStore->getCurrentScope();
            $subscriber = $observer->getEvent()->getSubscriber();

            if ($subscriber->getStatus() !== Subscriber::STATUS_UNSUBSCRIBED || empty($subscriber->getCustomerId())) {
                return $this;
            }

            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if ($pluginMode->isNewVersion()) {
                $this->apiService->createCustomer($subscriber->getCustomerId(), $scope);
            } else {
                $this->handleOldVersion($subscriber, $scope);
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function handleOldVersion($subscriber, Scope $scope): void
    {
        $registrationSettings = $this->subscribeViaRegistrationService->getSettings($scope);

        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings($scope->getScopeId())
        );

        if (!$registrationSettings->isEnabled() && !$newsletterSettings->isEnabled()) {
            return;
        }

        $this->contactService->removeContact(
            new RemoveContact($scope, $subscriber->getSubscriberEmail())
        );
    }
}
