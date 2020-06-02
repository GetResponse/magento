<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Account\AccountService;
use GrShareCode\TrackingCode\TrackingCodeService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;

class Save extends AbstractController
{
    private $repository;
    private $apiClientFactory;
    private $customFieldsMappingService;
    private $cacheManager;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        CustomFieldsMappingService $customFieldsMappingService,
        Manager $cacheManager
    ) {
        parent::__construct($context);

        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->cacheManager = $cacheManager;
    }

    public function execute()
    {
        parent::execute();
        $connectionSettings = ConnectionSettingsFactory::createFromPost(
            $this->request->getPostValue()
        );

        if ('' === $connectionSettings->getApiKey()) {
            return $this->redirect($this->_redirect->getRefererUrl(), Message::EMPTY_API_KEY, true);
        }

        try {
            $grApiClient = $this->apiClientFactory->createApiClientFromConnectionSettings($connectionSettings);
            $grApiClient->checkConnection();

            $accountService = new AccountService($grApiClient);

            $account = AccountFactory::createFromShareCodeAccount(
                $accountService->getAccount()
            );

            $trackingCodeService = new TrackingCodeService($grApiClient);
            $trackingCode = $trackingCodeService->getTrackingCode();

            $this->repository->saveConnectionSettings(
                $connectionSettings,
                $this->scope->getScopeId()
            );

            $this->repository->saveWebEventTracking(
                WebEventTrackingSettingsFactory::createFromArray([
                    'isEnabled' => false,
                    'isFeatureTrackingEnabled' => $trackingCode->isFeatureEnabled(),
                    'codeSnippet' => $trackingCode->getSnippet()
                ]),
                $this->scope->getScopeId()
            );
            $this->repository->saveAccountDetails($account, $this->scope->getScopeId());

            $this->customFieldsMappingService->setDefaultCustomFields($this->scope);

            $this->cacheManager->clean(['config']);

            return $this->redirect($this->_redirect->getRefererUrl(), Message::ACCOUNT_CONNECTED);

        } catch (Exception $e) {
            return $this->redirect($this->_redirect->getRefererUrl(), Message::API_ERROR_MESSAGE, true);
        }
    }
}
