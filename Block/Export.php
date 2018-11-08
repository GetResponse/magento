<?php

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Repository $repository,
        GetresponseApiClientFactory $apiClientFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return $this->repository->getCustomers();
    }

    /**
     * @return CustomFieldsCollection
     */
    public function getCustoms()
    {
        return CustomFieldsCollectionFactory::createFromRepository($this->repository->getCustoms());
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getCampaigns()
    {
        try {
            return (new ContactListService($this->apiClientFactory->createGetResponseApiClient()))->getAllContactLists();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return ShopsCollection|Redirect
     */
    public function getShops()
    {
        try {
            return (new ShopService($this->apiClientFactory->createGetResponseApiClient()))->getAllShops();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
