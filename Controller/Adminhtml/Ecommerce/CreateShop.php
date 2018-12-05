<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Shop\Command\AddShopCommand;
use GrShareCode\Shop\ShopService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class CreateShop
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class CreateShop extends AbstractController
{
    /** @var Repository */
    private $repository;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param ApiClientFactory $apiClientFactory
     * @param Repository $repository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (!isset($data['shop_name']) || strlen($data['shop_name']) === 0) {
            return $this->resultJsonFactory->create()->setData(['error' => Message::INCORRECT_SHOP_NAME]);
        }

        try {
            $countryCode = $this->repository->getMagentoCountryCode();
            $lang = substr($countryCode, 0, 2);
            $currency = $this->repository->getMagentoCurrencyCode();

            $apiClient = $this->apiClientFactory->createGetResponseApiClient();
            $service = new ShopService($apiClient);
            $shopId = $service->addShop(new AddShopCommand($data['shop_name'], $lang, $currency));
            return $this->resultJsonFactory->create()->setData(['shopId' => $shopId, 'name' => $data['shop_name']]);
        } catch (GetresponseApiException $e) {
            return $this->resultJsonFactory->create()->setData(['error' => $e->getMessage()]);
        } catch (ApiException $e) {
            return $this->resultJsonFactory->create()->setData(['error' => $e->getMessage()]);
        }
    }
}
