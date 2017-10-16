<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository as Repository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class CreateShop
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class CreateShop extends Action
{
    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param GrRepository $grRepository
     * @param Repository $repository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        GrRepository $grRepository,
        Repository $repository,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $grRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (!isset($data['shop_name']) || strlen($data['shop_name']) === 0) {
            return  $this->resultJsonFactory->create()->setData(['error' => 'Incorrect shop name']);
        }

        $countryCode = $this->repository->getMagentoCountryCode();
        $lang = substr($countryCode, 0, 2);
        $currency = $this->repository->getMagentoCurrencyCode();

        $result = $this->grRepository->createShop($data['shop_name'], $lang, $currency);
        return $this->resultJsonFactory->create()->setData($result);
    }
}
