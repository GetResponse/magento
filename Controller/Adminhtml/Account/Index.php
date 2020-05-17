<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractController
{
    const PAGE_TITLE = 'GetResponse Account';

    protected $resultPageFactory;
    private $magentoStore;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->magentoStore = $magentoStore;
    }

    public function execute()
    {
        if ($this->magentoStore->shouldRedirectToStore()) {
            return $this->redirectToStore(Route::ACCOUNT_INDEX_ROUTE);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
