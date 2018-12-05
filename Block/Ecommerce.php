<?php
namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger,
        Repository $repository
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $redirectFactory,
            $apiClientFactory,
            $logger
        );
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getShopStatus()
    {
        return $this->repository->getShopStatus();
    }

    /**
     * @return string
     */
    public function getCurrentShopId()
    {
        return $this->repository->getShopId();
    }

    /**
     * @return string
     */
    public function getEcommerceListId()
    {
        return $this->repository->getEcommerceListId();
    }

    /**
     * @return ShopsCollection|Redirect
     */
    public function getShops()
    {
        try {
            $apiClient = $this->getApiClientFactory()->createGetResponseApiClient();

            return (new ShopService($apiClient))->getAllShops();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getCampaigns()
    {
        try {
            return (new ContactListService($this->getApiClientFactory()->createGetResponseApiClient()))->getAllContactLists();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
