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

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getResponseBlock
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getResponseBlock
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->getResponseBlock = $getResponseBlock;
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
     * @return ContactListCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->repositoryFactory->createGetResponseApiClient()))->getAllContactLists();
    }

    /**
     * @return ShopsCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getShops()
    {
        return (new ShopService($this->repositoryFactory->createGetResponseApiClient()))->getAllShops();
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
    }
}
