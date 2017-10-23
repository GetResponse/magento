<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
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
     * @param Manager $cacheManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        Manager $cacheManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->cacheManager = $cacheManager;

    }


    /**
     * @return Redirect
     */
    public function execute()
    {
        $this->repository->clearDatabase();
        $this->cacheManager->clean(['config']);

        $this->messageManager->addSuccessMessage('GetResponse account disconnected');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }
}