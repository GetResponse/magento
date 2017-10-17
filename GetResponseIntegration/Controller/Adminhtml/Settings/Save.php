<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class Save
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Save extends Action
{
    const API_ERROR_MESSAGE = 'The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one';

    const API_EMPTY_VALUE_MESSAGE = 'You need to enter API key. This field can\'t be empty';

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var GrRepository */
    private $grRepository;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RepositoryFactory $repositoryFactory,
        Repository $repository,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->checkAccess()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->grRepository = $repositoryFactory->buildRepository();
        $this->repository = $repository;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('GetResponse account');

        $featureTracking = false;
        $trackingCodeSnippet = '';

        $data = $this->request->getPostValue();

        if (empty($data)) {
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

        $this->grRepository->createResource($apiKey, $apiUrl, $domain);
        $response = $this->grRepository->getAccountDetails();

        if (!isset($response->accountId)) {
            $this->messageManager->addErrorMessage(self::API_ERROR_MESSAGE);
            return $resultPage;
        }

        $features = $this->grRepository->getFeatures();

        if ($features instanceof \stdClass && $features->feature_tracking == 1) {
            $featureTracking = true;

            // getting tracking code
            $trackingCode = (array) $this->grRepository->getTrackingCode();

            if (!empty($trackingCode) && is_object($trackingCode[0]) && 0 < strlen($trackingCode[0]->snippet)) {
                $trackingCodeSnippet = $trackingCode[0]->snippet;
            }
        }

        $this->repository->saveAllSettings(
            $apiKey,
            $apiUrl,
            $domain,
            $featureTracking ? 'enabled' : 'disabled',
            $trackingCodeSnippet
        );

        $this->repository->saveAllAccountDetails(
            $response->accountId,
            $response->firstName,
            $response->lastName,
            $response->email,
            $response->companyName,
            $response->phone,
            $response->state,
            $response->city,
            $response->street,
            $response->zipCode,
            $response->countryCode->countryCode
        );

        $this->messageManager->addSuccessMessage('GetResponse account connected');
        return $resultPage;
    }
}