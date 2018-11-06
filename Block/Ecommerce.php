<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiException;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Getresponse */
    private $getResponseBlock;

    /** @var RedirectFactory */
    private $redirectFactory;

    /** @var ManagerInterface */
    private $messageManager;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getResponseBlock
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getResponseBlock,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->getResponseBlock = $getResponseBlock;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
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
            $apiClient = $this->repositoryFactory->createGetResponseApiClient();
            return (new ShopService($apiClient))->getAllShops();
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        }
    }

    /**
     * @return RegistrationSettings
     */
    public function getRegistrationSettings()
    {
        return $this->getResponseBlock->getRegistrationSettings();
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->repositoryFactory->createGetResponseApiClient()))->getAllContactLists();
    }
}
