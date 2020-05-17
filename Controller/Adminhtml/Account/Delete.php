<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;

class Delete extends AbstractController
{
    const BACK_URL = 'getresponse/account/index';

    private $repository;
    private $cacheManager;

    public function __construct(
        Context $context,
        Repository $repository,
        Manager $cacheManager
    ) {
        parent::__construct($context);

        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->cacheManager = $cacheManager;
    }

    public function execute()
    {
        $scopeId = $this->request->getParam(Config::SCOPE_TAG);
        $this->repository->clearDatabase($scopeId);
        $this->cacheManager->clean(['config']);

        $this->messageManager->addSuccessMessage(Message::ACCOUNT_DISCONNECTED);

        return $this->_redirect(self::BACK_URL);
    }
}
