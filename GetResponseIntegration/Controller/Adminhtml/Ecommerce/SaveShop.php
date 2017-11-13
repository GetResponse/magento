<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Backend\App\Action;

/**
 * Class SaveShop
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class SaveShop extends Action
{
    const BACK_URL = 'getresponseintegration/ecommerce/index';

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
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::INCORRECT_API_RESPONSE_MESSAGE);

            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (isset($data['e_commerce_status']) && '1' === $data['e_commerce_status']) {
            if (empty($data['shop_id'])) {
                $this->messageManager->addErrorMessage(Message::STORE_CHOOSE);

                return $resultRedirect;
            }

            $status = (isset($data['e_commerce_status']) && '1' === $data['e_commerce_status']) ? 'enabled' : 'disabled';

            $this->repository->saveShopStatus($status);
            $this->repository->saveShopId($data['shop_id']);
        } else {
            $this->repository->saveShopStatus('disabled');
            $this->repository->saveShopId(null);
        }

        $this->cache->cleanType('config');
        $this->messageManager->addSuccessMessage(Message::ECOMMERCE_SAVED);

        return $resultRedirect;
    }
}
