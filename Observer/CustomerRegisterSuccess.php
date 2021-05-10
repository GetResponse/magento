<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;

class CustomerRegisterSuccess implements ObserverInterface
{
    private $request;
    private $magentoSubscriber;
    private $logger;
    private $repository;

    public function __construct(
        RequestInterface $request,
        MagentoSubscriber $magentoSubscriber,
        Repository $repository,
        Logger $logger
    ) {
        $this->request = $request;
        $this->magentoSubscriber = $magentoSubscriber;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): CustomerRegisterSuccess
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            if (!$pluginMode->isNewVersion()) {
                return $this;
            }

            $scope = new Scope($observer->getCustomer()->getStoreId());
//            $scope = $this->magentoStore->getCurrentScope();
            $liveSynchronization = LiveSynchronization::createFromRepository(
                $this->repository->getLiveSynchronization($scope->getScopeId())
            );

            if (!$liveSynchronization->shouldImportCustomer()) {
                return $this;
            }

            $subscriptionOption = $this->request->getParam('is_subscribed');
            if ((int)$subscriptionOption === Subscriber::STATUS_SUBSCRIBED) {
                $customerId = (int)$observer->getCustomer()->getId();
                $this->magentoSubscriber->subscribeCustomerById($customerId);
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

}
