<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiException;
use GrShareCode\Shop\ShopsCollection;
use GrShareCode\Shop\ShopService;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GetresponseApiClient */
    private $grApiClient;

    /** @var Getresponse */
    private $getResponseBlock;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getResponseBlock
     *
     * @throws RepositoryException
     * @throws ApiTypeException
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getResponseBlock
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grApiClient = $repositoryFactory->createGetResponseApiClient();
        $this->getResponseBlock = $getResponseBlock;
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
     * @return ShopsCollection
     * @throws GetresponseApiException
     */
    public function getShops()
    {
        return (new ShopService($this->grApiClient))->getAllShops();
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
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->grApiClient))->getAllContactLists();
    }
}
