<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiException;
use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Newsletter
 * @package GetResponse\GetResponseIntegration\Block
 */
class Newsletter extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

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
        $this->repositoryFactory = $repositoryFactory;
        $this->grApiClient = $repositoryFactory->createGetResponseApiClient();
        $this->getResponseBlock = $getResponseBlock;
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     */
    public function getLists()
    {
        return (new ContactListService($this->grApiClient))->getAllContactLists();
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
    }

    public function getNewsletterSettings()
    {
        return $this->getResponseBlock->getNewsletterSettings();
    }
}
