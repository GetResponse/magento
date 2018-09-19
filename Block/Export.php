<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
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
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends Template
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
     */
    public function getCampaigns()
    {
        $service = new ContactListService($this->grApiClient);
        return $service->getAllContactLists();
    }

    /**
     * @return ShopsCollection
     * @throws GetresponseApiException
     */
    public function getShops()
    {
        $service = new ShopService($this->grApiClient);
        return $service->getAllShops();
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
    }
}
