<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Cache\Manager;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Delete extends Action
{
    const BACK_URL = 'getresponseintegration/settings/index';

    /** @var Repository */
    private $repository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** @var Manager */
    private $cacheManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param AccessValidator $accessValidator
     * @param Manager $cacheManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        AccessValidator $accessValidator,
        Manager $cacheManager
    ) {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->cacheManager = $cacheManager;
    }


    /**
     * @return Redirect
     */
    public function execute()
    {
        $this->repository->clearConnectionSettings();
        $this->repository->clearRegistrationSettings();
        $this->repository->clearAccountDetails();
        $this->repository->clearWebforms();
        $this->repository->clearRules();
        $this->repository->clearWebEventTracking();
        $this->repository->clearCustoms();

        $this->cacheManager->clean(['config']);

        $this->messageManager->addSuccessMessage('GetResponse account disconnected');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }
}