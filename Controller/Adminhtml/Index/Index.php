<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public function execute(): Page
    {
        $pageFactory = $this->_objectManager->get(PageFactory::class);

        $resultPage = $pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('GetResponse');

        return $resultPage;
    }
}
