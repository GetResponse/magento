<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\EcommerceSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ValidationException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;

class Save extends AbstractController
{
    private $cache;
    private $repository;

    public function __construct(
        Context $context,
        TypeListInterface $cache,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->cache = $cache;
        $this->repository = $repository;
    }

    public function execute()
    {
        parent::execute();

        if (!$this->isConnected()) {
            return $this->redirectToStore(Route::ACCOUNT_INDEX_ROUTE);
        }

        try {
            $settings = EcommerceSettingsFactory::createFromPost($this->request->getPostValue());

            $this->repository->saveShopStatus($settings->getStatus(), $this->scope->getScopeId());
            $this->repository->saveShopId($settings->getShopId(), $this->scope->getScopeId());
            $this->repository->saveEcommerceListId($settings->getListId(), $this->scope->getScopeId());

            $this->cache->cleanType('config');

            return $this->redirect($this->_redirect->getRefererUrl(), Message::ECOMMERCE_SAVED);
        } catch (ValidationException $e) {
            return $this->redirect($this->_redirect->getRefererUrl(), $e->getMessage(), true);
        }
    }
}
