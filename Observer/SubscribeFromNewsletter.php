<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class SubscribeFromNewsletter implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $logger;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        Logger $logger
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer): SubscribeFromNewsletter
    {
        try {
            /** @var Subscriber $subscriber */
            $subscriber = $observer->getSubscriber();
            $scope = new Scope($subscriber->getStoreId());
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if (!$pluginMode->isNewVersion()) {
                $this->handleOldVersion($scope, $subscriber->getEmail());
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
    private function handleOldVersion(Scope $scope, string $email): void
    {
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings($scope->getScopeId())
        );

        if (!$newsletterSettings->isEnabled()) {
            return;
        }
        $this->contactService->addContact(
            new AddContact(
                $scope,
                $email,
                '',
                '',
                $newsletterSettings->getCampaignId(),
                $newsletterSettings->getCycleDay(),
                new ContactCustomFieldsCollection(),
                false
            )
        );
    }
}
