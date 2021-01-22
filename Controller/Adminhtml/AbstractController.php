<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractController extends Action
{
    /** @var Http; */
    protected $request;
    protected $scope;
    /** @var MagentoStore */
    protected $magentoStore;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->request = $this->getRequest();

        $this->magentoStore = $this->_objectManager->get(MagentoStore::class);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $scopeId = $this->magentoStore->getStoreIdFromUrl();

        if (null === $scopeId) {
            $scopeId = $this->magentoStore->getDefaultStoreId();
        }

        $this->scope = new Scope($scopeId);
    }

    /**
     * @param string $pageTitle
     * @return Page
     */
    public function render(string $pageTitle): Page
    {
        $pageFactory = $this->_objectManager->get(PageFactory::class);

        $resultPage = $pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }

    /**
     * @param array $response
     * @return Json
     */
    public function renderJson(array $response): Json
    {
        $jsonFactory = $this->_objectManager->get(JsonFactory::class);
        return $jsonFactory->create()->setData($response);
    }

    /**
     * @param string $path
     * @param string $message
     * @param bool $isError
     * @return ResponseInterface
     */
    public function redirect(
        string $path,
        string $message = '',
        bool $isError = false
    ): ResponseInterface {
        if (!empty($message)) {
            if ($isError) {
                $this->messageManager->addErrorMessage($message);
            } else {
                $this->messageManager->addSuccessMessage($message);
            }
        }

        return $this->_redirect($path);
    }

    /**
     * @param string $path
     * @return ResponseInterface
     */
    protected function redirectToStore(string $path): ResponseInterface
    {
        $storeId = $this->_session->getGrScope();
        $path .= '/' . Config::SCOPE_TAG . '/' . $storeId;
        return $this->_redirect($path);
    }

    /**
     * @return bool
     */
    protected function shouldRedirectToStore(): bool
    {
        return $this->magentoStore->shouldRedirectToStore();
    }

    /**
     * @return bool
     */
    protected function isConnected(): bool
    {
        $accountReadModel = $this->_objectManager->get(AccountReadModel::class);
        return $accountReadModel->isConnected($this->scope);
    }
}
