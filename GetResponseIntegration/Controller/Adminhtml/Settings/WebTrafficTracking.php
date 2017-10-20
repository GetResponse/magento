<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class WebTrafficTracking
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class WebTrafficTracking extends Action
{
    const BACK_URL = 'getresponseintegration/settings/webtraffictracking';
    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    /**
     * @return Redirect|Page
     */
    public function execute()
    {
        $data = $this->request->getPostValue();

        if (isset($data['updateWebTraffic'])) {

            $webEventTracking = WebEventTrackingFactory::buildFromRepository(
                $this->repository->getWebEventTracking()
            );

            $newWebEventTracking = WebEventTrackingFactory::buildFromParams(
                (isset($data['web_traffic']) && 1 === (int) $data['web_traffic']) ? 1 : 0,
                $webEventTracking->isFeatureTrackingEnabled(),
                $webEventTracking->getCodeSnippet()
            );

            $this->repository->saveWebEventTracking($newWebEventTracking);

            $message = ($newWebEventTracking->isEnabled()) ? 'Web event traffic tracking enabled' : 'Web event traffic tracking disabled';
            $this->messageManager->addSuccessMessage($message);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('Web Event Tracking');

        return $resultPage;
    }
}
