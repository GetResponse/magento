<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\EcommerceSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ValidationException;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Request\Http;

class Save extends AbstractController
{
    private $cache;
    private $repository;
    private $magentoStore;

    public function __construct(
        Context $context,
        TypeListInterface $cache,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->cache = $cache;
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(Route::ECOMMERCE_INDEX_ROUTE);
        $scopeId = $this->magentoStore->getStoreIdFromUrl();

        try {
            /** @var Http $request */
            $request = $this->getRequest();
            $settings = EcommerceSettingsFactory::createFromPost($request->getPostValue());

            $this->repository->saveShopStatus($settings->getStatus(), $scopeId);
            $this->repository->saveShopId($settings->getShopId(), $scopeId);
            $this->repository->saveEcommerceListId($settings->getListId(), $scopeId);

            $this->cache->cleanType('config');
            $this->messageManager->addSuccessMessage(Message::ECOMMERCE_SAVED);
        } catch (ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
