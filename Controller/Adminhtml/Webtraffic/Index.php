<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webtraffic;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\PageTitle;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Cache\Type;

class Index extends AbstractController
{
    private $repository;
    private $cacheTypeList;

    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $cacheTypeList;
        $this->repository = $repository;
    }

    public function execute()
    {
        parent::execute();

        if ($this->shouldRedirectToStore()) {
            return $this->redirectToStore(Route::WEB_TRAFFIC_INDEX_ROUTE);
        }

        $data = $this->request->getPostValue();

        if (isset($data['updateWebTraffic'])) {

            $webEventTracking = WebEventTracking::createFromRepository(
                $this->repository->getWebEventTracking($this->scope->getScopeId())
            );

            $params = [
                'isEnabled' => (isset($data['web_traffic']) && 1 === (int)$data['web_traffic']),
                'isFeatureTrackingEnabled' => $webEventTracking->isFeatureTrackingEnabled(),
                'codeSnippet' => $webEventTracking->getCodeSnippet()
            ];

            $newWebEventTracking = WebEventTracking::createFromArray($params);

            $this->repository->saveWebEventTracking($newWebEventTracking, $this->scope->getScopeId());

            $this->cacheTypeList->cleanType(Type::TYPE_IDENTIFIER);

            $message = ($newWebEventTracking->isActive()) ? Message::WEB_EVENT_TRAFFIC_ENABLED : Message::WEB_EVENT_TRAFFIC_DISABLED;

            return $this->redirect($this->_redirect->getRefererUrl(), $message);
        }

        return $this->render(PageTitle::WEB_TRAFFIC);
    }
}
