<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webtraffic;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Cache\Type;

class Index extends AbstractController
{
    const PAGE_TITLE = 'Web Event Tracking';

    private $resultPageFactory;
    private $repository;
    private $cacheTypeList;
    private $magentoStore;
    private $accountReadModel;

    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        PageFactory $resultPageFactory,
        Repository $repository,
        MagentoStore $magentoStore,
        AccountReadModel $accountReadModel
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $cacheTypeList;
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
        $this->accountReadModel = $accountReadModel;
    }

    public function execute()
    {
        if ($this->magentoStore->shouldRedirectToStore()) {
            return $this->redirectToStore(Route::WEB_TRAFFIC_INDEX_ROUTE);
        }

        $scope = new Scope($this->magentoStore->getStoreIdFromUrl());

        if (!$this->accountReadModel->isConnected($scope)) {
            return $this->redirectToStore(Config::PLUGIN_MAIN_PAGE);
        }

        $data = $this->request->getPostValue();
        $scopeId = $this->magentoStore->getStoreIdFromUrl();

        if (isset($data['updateWebTraffic'])) {

            $webEventTracking = WebEventTrackingSettingsFactory::createFromArray(
                $this->repository->getWebEventTracking($scopeId)
            );

            $params = [
                'isEnabled' => (isset($data['web_traffic']) && 1 === (int)$data['web_traffic']),
                'isFeatureTrackingEnabled' => $webEventTracking->isFeatureTrackingEnabled(),
                'codeSnippet' => $webEventTracking->getCodeSnippet()
            ];

            $newWebEventTracking = WebEventTrackingSettingsFactory::createFromArray($params);

            $this->repository->saveWebEventTracking($newWebEventTracking, $scopeId);

            $this->cacheTypeList->cleanType(Type::TYPE_IDENTIFIER);

            $message = ($newWebEventTracking->isEnabled()) ? Message::WEB_EVENT_TRAFFIC_ENABLED : Message::WEB_EVENT_TRAFFIC_DISABLED;
            $this->messageManager->addSuccessMessage($message);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(Route::WEB_TRAFFIC_INDEX_ROUTE);

            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
