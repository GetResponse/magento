<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;

/**
 * Class Settings
 * @package GetResponse\GetResponseIntegration\Block
 */
class Settings extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Getresponse */
    private $getresponseBlock;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getresponseBlock
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getresponseBlock
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->getresponseBlock = $getresponseBlock;
    }

    /**
     * @return mixed
     */
    public function getAccountInfo()
    {
        return $this->getresponseBlock->getAccountInfo();
    }
}
