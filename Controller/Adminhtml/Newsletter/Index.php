<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Newsletter;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractController
{
    const PAGE_TITLE = 'Add to Contact List after Customer Subscribes';

    private $resultPageFactory;
    private $magentoStore;
    private $accountReadModel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MagentoStore $magentoStore,
        AccountReadModel $accountReadModel
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->magentoStore = $magentoStore;
        $this->accountReadModel = $accountReadModel;
    }

    public function execute()
    {
        if ($this->magentoStore->shouldRedirectToStore()) {
            return $this->redirectToStore(Route::NEWSLETTER_INDEX_ROUTE);
        }

        $scope = new Scope($this->magentoStore->getStoreIdFromUrl());

        if (!$this->accountReadModel->isConnected($scope)) {
            return $this->redirectToStore(Config::PLUGIN_MAIN_PAGE);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
