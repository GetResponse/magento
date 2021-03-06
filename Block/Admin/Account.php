<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\Account as GetresponseAccount;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template\Context;

class Account extends AdminTemplate
{
    private $accountReadModel;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore,
        AccountReadModel $accountReadModel
    ) {
        parent::__construct($context, $magentoStore);

        $this->accountReadModel = $accountReadModel;
        $this->routePrefix = Route::ACCOUNT_INDEX_ROUTE;
    }

    public function getAccountInfo(): GetresponseAccount
    {
        return $this->accountReadModel->getAccount($this->getScope());
    }

    public function isConnectedToGetResponse(): bool
    {
        return $this->accountReadModel->isConnected($this->getScope());
    }

    public function getLastPostedApiKey(): string
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data) && isset($data['api_key'])) {
            return $data['api_key'];
        }

        return '';
    }

    public function getLastPostedTypeOfAccountCheckboxValue(): int
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['is_mx']) && 1 === $data['is_mx']) {
            return 1;
        }

        return 0;
    }

    public function getLastPostedApiUrl(): string
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['api_url'])) {
            return $data['api_url'];
        }

        return '';
    }

    public function getLastPostedApiDomain(): string
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }

        return '';
    }

    public function getHiddenApiKey(): string
    {
        return $this->accountReadModel->getHiddenApiKey($this->getScope());
    }
}
