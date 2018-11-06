<?php

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiClient;
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
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GetresponseApiClient */
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
     * @return RegistrationSettings
     */
    public function getExportSettings()
    {
        return $this->getResponseBlock->getRegistrationSettings();
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
        return $this->getResponseBlock->getCustoms();
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getCampaigns()
    {
        try {
            return (new ContactListService($this->repositoryFactory->createGetResponseApiClient()))->getAllContactLists();
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        }
    }

    /**
     * @return ShopsCollection|Redirect
     */
    public function getShops()
    {
        try {
            return (new ShopService($this->repositoryFactory->createGetResponseApiClient()))->getAllShops();
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        }
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
    }
}
