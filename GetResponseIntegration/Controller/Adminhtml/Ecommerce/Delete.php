<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends Action
{
    /** @var GrRepository */
    private $grRepository;

    const BACK_URL = 'getresponseintegration/ecommerce/index';

    /**
     * @param Context $context
     * @param RepositoryFactory $repositoryFactory
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        RepositoryFactory $repositoryFactory,
        AccessValidator $accessValidator
    ) {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->grRepository = $repositoryFactory->createRepository();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage('Incorrect shop');
            $resultRedirect->setPath(self::BACK_URL);

            return $resultRedirect;
        }

        $response = $this->grRepository->deleteShop($id);

        if (isset($response->httpStatus) && $response->httpStatus > 204) {
            $this->messageManager->addErrorMessage($response->codeDescription . ' - uuid: ' . $response->uuid);
        } else {
            $this->messageManager->addSuccessMessage('Store removed');
        }

        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }
}
