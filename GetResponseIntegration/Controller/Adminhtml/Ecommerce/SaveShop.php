<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\Http;

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

    /**
     * @param Context $context
     * @param TypeListInterface $cache
     * @param Repository $repository
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        TypeListInterface $cache,
        Repository $repository,
        AccessValidator $accessValidator
    ) {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (isset($data['e_commerce_status']) && '1' === $data['e_commerce_status']) {

            if (empty($data['shop_id'])) {
                $this->messageManager->addErrorMessage('You need to choose a store');

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
        $this->messageManager->addSuccessMessage('Ecommerce settings saved');

        return $resultRedirect;
    }
}