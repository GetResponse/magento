<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;

class Delete extends AbstractController
{
    private $repository;
    private $cacheManager;

    public function __construct(
        Context $context,
        Repository $repository,
        Manager $cacheManager
    ) {
        parent::__construct($context);

        $this->repository = $repository;
        $this->cacheManager = $cacheManager;
    }

    public function execute()
    {
        parent::execute();
        $this->repository->clearDatabase($this->scope->getScopeId());
        $this->cacheManager->clean(['config']);

        return $this->redirect($this->_redirect->getRefererUrl(), Message::ACCOUNT_DISCONNECTED);
    }
}
