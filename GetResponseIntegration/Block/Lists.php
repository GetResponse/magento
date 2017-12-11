<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\View\Element\Template;

/**
 * Class Lists
 * @package GetResponse\GetResponseIntegration\Block
 */
class Lists extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->createRepository();
    }

    /**
     * @return mixed
     */
    public function getAccountFromFields()
    {
        return $this->grRepository->getAccountFromFields();
    }

    /**
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject()
    {
        $countryCode = $this->repository->getMagentoCountryCode();
        $lang = substr($countryCode, 0, 2);

        return $this->grRepository->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody()
    {
        $countryCode = $this->repository->getMagentoCountryCode();
        $lang = substr($countryCode, 0, 2);

        return $this->grRepository->getSubscriptionConfirmationsBody($lang);
    }

    public function getBackUrl()
    {
        return $this->createBackUrl($this->getRequest()->getParam('back'));
    }

    /**
     * @param string $back
     *
     * @return string
     */
    private function createBackUrl($back)
    {
        switch ($back) {
            case 'export':
                return 'getresponseintegration/export/index';
                break;

            case 'registration':
                return 'getresponseintegration/settings/registration';
                break;
        }

        return '';
    }
}
