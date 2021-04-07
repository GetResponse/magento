<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Exception;

class PreDispatchObserver implements ObserverInterface
{
    private $urlInterface;
    private $messageManager;
    private $actionFlag;
    private $accountReadModel;
    private $magentoStore;
    private $repository;
    private $logger;

    public function __construct(
        UrlInterface $urlInterface,
        ManagerInterface $messageManager,
        ActionFlag $actionFlag,
        AccountReadModel $accountReadModel,
        MagentoStore $magentoStore,
        Repository $repository,
        Logger $logger
    ) {
        $this->urlInterface = $urlInterface;
        $this->messageManager = $messageManager;
        $this->actionFlag = $actionFlag;
        $this->accountReadModel = $accountReadModel;
        $this->magentoStore = $magentoStore;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer): PreDispatchObserver
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if ($pluginMode->isNewVersion()) {
                if (!$this->amIOnTransitionPage()) {
                    $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                    $observer->getControllerAction()->getResponse()->setRedirect(
                        $this->urlInterface->getUrl(Route::TRANSITION_PAGE_ROUTE)
                    );
                }
            } elseif (!$this->amIOnAccountPage() && !$this->accountReadModel->isConnected(new Scope($this->getScope()))) {
                $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
                $url = $this->urlInterface->getUrl(Route::ACCOUNT_INDEX_ROUTE);
                $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                $observer->getControllerAction()->getResponse()->setRedirect($url);
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

    private function getScope(): int
    {
        $scopeId = $this->magentoStore->getStoreIdFromUrl();

        if (null === $scopeId) {
            $scopeId = $this->magentoStore->getStoreIdFromSession();
        }

        return (int)$scopeId;
    }

    private function amIOnAccountPage(): bool
    {
        return (bool)preg_match('/getresponse\/account/i', $this->urlInterface->getCurrentUrl());
    }

    private function amIOnTransitionPage(): bool
    {
        return (bool)preg_match('/getresponse\/transition/i', $this->urlInterface->getCurrentUrl());
    }
}
