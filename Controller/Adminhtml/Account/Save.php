<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Message;
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
    const BACK_URL = 'getresponse/account/index';

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
        $trackingCodeSnippet = $apiUrl = $domain = '';

        $connectionSettings = ConnectionSettingsFactory::createFromPost($this->request->getPostValue());

        if ('' == $connectionSettings->getApiKey()) {
            $this->messageManager->addErrorMessage(Message::EMPTY_API_KEY);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $grRepository = $this->repositoryFactory->createFromConnectionSettings($connectionSettings);
        if (false === $this->repositoryValidator->validateGrRepository($grRepository)) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $account = AccountFactory::createFromArray($grRepository->getAccountDetails());

        if (empty($account->getAccountId())) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $features = $grRepository->getFeatures();

        if (isset($features['feature_tracking']) && (int) $features['feature_tracking'] === 1) {
            $featureTracking = true;

            $trackingCode = $grRepository->getTrackingCode();

            if (isset($trackingCode[0]) && 0 < strlen($trackingCode[0]['snippet'])) {
                $trackingCodeSnippet = $trackingCode[0]['snippet'];
            }
        }

        $this->repository->saveConnectionSettings($connectionSettings);

        $this->repository->saveWebEventTracking(
            WebEventTrackingSettingsFactory::createFromArray([
                'isEnabled' => false,
                'isFeatureTrackingEnabled' => $featureTracking,
                'codeSnippet' => $trackingCodeSnippet
            ])
        );
        $this->repository->saveAccountDetails($account);
        $this->repository->setCustomsOnInit(DefaultCustomFieldsFactory::createDefaultCustomsMap());
        $this->messageManager->addSuccessMessage(Message::ACCOUNT_CONNECTED);

        return $this->_redirect(self::BACK_URL);
    }
}
