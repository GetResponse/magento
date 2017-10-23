<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\DefaultCustomFieldsFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class Save
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Save extends Action
{
    const BACK_URL = 'getresponseintegration/settings/index';

    const PAGE_TITLE = 'GetResponse account';

    const API_ERROR_MESSAGE = 'The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one';

    const API_EMPTY_VALUE_MESSAGE = 'You need to enter API key. This field can\'t be empty';

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     * @param Manager $cacheManager
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RepositoryFactory $repositoryFactory,
        Repository $repository,
        Manager $cacheManager,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->repositoryValidator = $repositoryValidator;
    }


    /**
     * @return ResponseInterface|Page
     */
    public function execute()
    {
        $featureTracking = false;
        $trackingCodeSnippet = '';

        $data = $this->request->getPostValue();

        if (empty($data)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        if (empty($data['getresponse_api_key'])) {
            $this->messageManager->addErrorMessage(self::API_EMPTY_VALUE_MESSAGE);
        }

        $apiKey = $data['getresponse_api_key'];
        $apiUrl = null;
        $domain = null;

        if (isset($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
            $apiUrl = !empty($data['getresponse_api_url']) ? $data['getresponse_api_url'] : null;
            $domain = !empty($data['getresponse_api_domain']) ? $data['getresponse_api_domain'] : null;
        }

        $grRepository = $this->repositoryFactory->createNewRepository($apiKey, $apiUrl, $domain);
        if (false === $this->repositoryValidator->validateGrRepository($grRepository)) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);

            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $account = AccountFactory::createFromArray($grRepository->getAccountDetails());

        if (empty($account->getAccountId())) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $features = $grRepository->getFeatures();

        if ($features instanceof \stdClass && $features->feature_tracking == 1) {
            $featureTracking = true;

            $trackingCode = (array)$grRepository->getTrackingCode();

            if (!empty($trackingCode) && is_object($trackingCode[0]) && 0 < strlen($trackingCode[0]->snippet)) {
                $trackingCodeSnippet = $trackingCode[0]->snippet;
            }
        }

        $payload = [
            'apiKey' => $apiKey,
            'url' => $apiUrl,
            'domain' => $domain
        ];

        $this->repository->saveConnectionSettings(
            ConnectionSettingsFactory::createFromArray($payload)
        );

        $params = [
            'isEnabled' => false,
            'isFeatureTrackingEnabled' => $featureTracking,
            'codeSnippet' => $trackingCodeSnippet
        ];

        $this->repository->saveWebEventTracking(
            WebEventTrackingSettingsFactory::createFromArray($params)
        );
        $this->repository->saveAccountDetails($account);

        $this->repository->setCustomsOnInit(DefaultCustomFieldsFactory::createDefaultCustomsMap());

        $this->messageManager->addSuccessMessage('GetResponse account connected');

        return $this->_redirect(self::BACK_URL);
    }
}
