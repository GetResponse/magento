<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Account;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Account\AccountService;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\TrackingCode\TrackingCodeService;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\DefaultCustomFieldsFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action\Context;
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

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        GetresponseApiClientFactory $apiClientFactory,
        Repository $repository
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
    }


    /**
     * @return ResponseInterface|Page
     */
    public function execute()
    {
        $connectionSettings = ConnectionSettingsFactory::createFromPost($this->request->getPostValue());

        if ('' == $connectionSettings->getApiKey()) {
            $this->messageManager->addErrorMessage(Message::EMPTY_API_KEY);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        try {
            $grApiClient = $this->apiClientFactory->createApiClientFromConnectionSettings($connectionSettings);
            $grApiClient->checkConnection();

            $accountService = new AccountService($grApiClient);
            $account = $accountService->getAccount();

            $trackingCodeService = new TrackingCodeService($grApiClient);

            $trackingCode = $trackingCodeService->getTrackingCode();

            $this->repository->saveConnectionSettings($connectionSettings);

            $this->repository->saveWebEventTracking(
                WebEventTrackingSettingsFactory::createFromArray([
                    'isEnabled' => false,
                    'isFeatureTrackingEnabled' => $trackingCode->isFeatureEnabled(),
                    'codeSnippet' => $trackingCode->getSnippet()
                ])
            );
            $this->repository->saveAccountDetails($account);
            $this->repository->setCustomsOnInit(DefaultCustomFieldsFactory::createDefaultCustomsMap());
            $this->messageManager->addSuccessMessage(Message::ACCOUNT_CONNECTED);

            return $this->_redirect(self::BACK_URL);

        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        } catch (ApiTypeException $e) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }
    }
}
