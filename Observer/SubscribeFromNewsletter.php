<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class SubscribeFromNewsletter implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $magentoStore;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        MagentoStore $magentoStore
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->magentoStore = $magentoStore;
    }

    public function execute(EventObserver $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings($scope->getScopeId())
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
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }
    }
}
