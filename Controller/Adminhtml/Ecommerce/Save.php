<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\EcommerceSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ValidationException;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\Http;
use GetResponse\GetResponseIntegration\Helper\Config;

/**
 * Class Save
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Save extends AbstractController
{
    const BACK_URL = 'getresponse/ecommerce/index';

    /** @var TypeListInterface */
    private $cache;

    /** @var Repository */
    private $repository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param TypeListInterface $cache
     * @param Repository $repository
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        TypeListInterface $cache,
        Repository $repository,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->cache = $cache;
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        try {
            if (!$this->repositoryValidator->validate()) {
                $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
                return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
            }

            /** @var Http $request */
            $request = $this->getRequest();
            $settings = EcommerceSettingsFactory::createFromPost($request->getPostValue());

            $this->repository->saveShopStatus($settings->getStatus());
            $this->repository->saveShopId($settings->getStoreId());
            $this->repository->saveEcommerceListId($settings->getListId());

            $this->cache->cleanType('config');
            $this->messageManager->addSuccessMessage(Message::ECOMMERCE_SAVED);
        } catch (ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
