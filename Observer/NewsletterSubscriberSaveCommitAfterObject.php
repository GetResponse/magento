<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class NewsletterSubscriberSaveCommitAfterObject implements ObserverInterface
{
    private $logger;
    private $apiService;

    public function __construct(
        Logger $logger,
        ApiService $apiService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    public function execute(Observer $observer): NewsletterSubscriberSaveCommitAfterObject
    {
        try {
            /** @var Subscriber $subscriber */
            $subscriber = $observer->getSubscriber();

            if (null === $subscriber) {
                $this->logger->addNotice('Subscriber in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            $scope = new Scope($subscriber->getStoreId());
            $customerId = $subscriber->getCustomerId();

            if (!empty($customerId)) {
                $this->apiService->upsertCustomerSubscription($subscriber, $scope);
                return $this;
            }

            $this->apiService->upsertSubscriber($subscriber, $scope);
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
