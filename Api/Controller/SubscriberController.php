<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberSearchResultsInterface;
use GetResponse\GetResponseIntegration\Api\SubscriberRepositoryInterface;
use GetResponse\GetResponseIntegration\Application\Magento\Newsletter\NewsletterUnsubscribeService;
use GetResponse\GetResponseIntegration\Controller\Api\SubscriberControllerInterface;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Api\SearchCriteriaInterface;

class SubscriberController extends ApiController implements SubscriberControllerInterface
{
    /** @var SubscriberRepositoryInterface */
    private $subscriberRepository;
    /** @var NewsletterUnsubscribeService */
    private $newsletterUnsubscribeService;

    /**
     * @param MagentoStore $magentoStore
     * @param SubscriberRepositoryInterface $subscriberRepository
     * @param NewsletterUnsubscribeService $newsletterUnsubscribeService
     */
    public function __construct(
        MagentoStore $magentoStore,
        SubscriberRepositoryInterface $subscriberRepository,
        NewsletterUnsubscribeService $newsletterUnsubscribeService
    ) {
        parent::__construct($magentoStore);
        $this->subscriberRepository = $subscriberRepository;
        $this->newsletterUnsubscribeService = $newsletterUnsubscribeService;
    }

    /**
     * @inheritdoc
     */
    public function getSubscribersWithoutCustomers(
        string $scope,
        SearchCriteriaInterface $searchCriteria
    ): NewsletterSubscriberSearchResultsInterface {
        $scopeId = (int) $scope;
        $this->verifyScope($scopeId);

        return $this->subscriberRepository->getListWithoutCustomers($scopeId, $searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function unsubscribe(string $scope, string $email): void
    {
        $scope = (int) $scope;
        $this->verifyScope($scope);

        $this->newsletterUnsubscribeService->unsubscribe($email, $scope);
    }
}
