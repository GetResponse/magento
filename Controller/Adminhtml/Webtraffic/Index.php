<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webtraffic;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\Config;

/**
 * Class Index
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Webtraffic
 */
class Index extends AbstractController
{
    const PAGE_TITLE = 'Web Event Tracking';
    const BACK_URL = 'getresponse/webtraffic/index';

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface|Redirect|Page
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $data = $this->request->getPostValue();

        if (isset($data['updateWebTraffic'])) {
            $webEventTracking = WebEventTrackingSettingsFactory::createFromArray(
                $this->repository->getWebEventTracking()
            );

            $params = [
                'isEnabled' => (isset($data['web_traffic']) && 1 === (int)$data['web_traffic']),
                'isFeatureTrackingEnabled' => $webEventTracking->isFeatureTrackingEnabled(),
                'codeSnippet' => $webEventTracking->getCodeSnippet()
            ];

            $newWebEventTracking = WebEventTrackingSettingsFactory::createFromArray($params);

            $this->repository->saveWebEventTracking($newWebEventTracking);

            $message = ($newWebEventTracking->isEnabled()) ? Message::WEB_EVENT_TRAFFIC_ENABLED : Message::WEB_EVENT_TRAFFIC_DISABLED;
            $this->messageManager->addSuccessMessage($message);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::BACK_URL);

            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
