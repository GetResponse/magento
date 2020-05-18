<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class AdminCheckAuthorization implements ObserverInterface
{
    private $urlInterface;
    private $messageManager;
    private $actionFlag;
    private $storeManager;
    private $accountReadModel;

    public function __construct(
        UrlInterface $urlInterface,
        ManagerInterface $messageManager,
        ActionFlag $actionFlag,
        StoreManagerInterface $storeManager,
        AccountReadModel $accountReadModel
    ) {
        $this->urlInterface = $urlInterface;
        $this->messageManager = $messageManager;
        $this->actionFlag = $actionFlag;
        $this->storeManager = $storeManager;
        $this->accountReadModel = $accountReadModel;
    }

    public function execute(EventObserver $observer): AdminCheckAuthorization
    {
        $scopeId = $this->storeManager->getStore()->getId();

        if ($this->isCurrentUrlWhitelisted()) {
            return $this;
        }

        if (!$this->accountReadModel->isConnected(new Scope($scopeId))) {
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
            $url = $this->urlInterface->getUrl(Config::PLUGIN_MAIN_PAGE);
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $observer->getControllerAction()->getResponse()->setRedirect($url);
        }

        return $this;
    }

    private function isCurrentUrlWhitelisted(): bool
    {
        return (bool) preg_match('/getresponse\/account/i', $this->urlInterface->getCurrentUrl());
    }
}
