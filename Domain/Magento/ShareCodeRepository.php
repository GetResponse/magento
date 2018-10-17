<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Model\CartMap;
use GetResponse\GetResponseIntegration\Model\OrderMap;
use GetResponse\GetResponseIntegration\Model\ProductMap;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\Job\Job;
use GrShareCode\Job\JobCollection;
use GrShareCode\ProductMapping\ProductMapping;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Store\Model\Store;


/**
 * Class ShareCodeRepository
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class ShareCodeRepository implements DbRepositoryInterface
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var WriterInterface */
    private $configWriter;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @param string $grShopId
     * @param int $externalProductId
     * @param int $externalVariantId
     * @return ProductMapping
     */
    public function getProductMappingByVariantId($grShopId, $externalProductId, $externalVariantId)
    {
        $productMap = $this->objectManager->get(ProductMap::class);

        $results = $productMap->getCollection()
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->addFieldToFilter('magento_product_id', $externalProductId)
            ->addFieldToFilter('magento_variant_id', $externalVariantId)
            ->getFirstItem();

        if (empty($results->getData())) {
            return new ProductMapping(null, null, null, null, null);
        }

        return new ProductMapping(
            $results->getMagentoProductId(),
            $results->getMagentoVariantId(),
            $results->getGrShopId(),
            $results->getGrProductId(),
            $results->getGrVariantId()
        );

    }

    /**
     * @param string $grShopId
     * @param int $externalProductId
     * @return ProductMapping
     */
    public function getProductMappingByProductId($grShopId, $externalProductId)
    {
        $productMap = $this->objectManager->get(ProductMap::class);

        $results = $productMap->getCollection()
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->addFieldToFilter('magento_product_id', $externalProductId)
            ->getFirstItem();

        if (empty($results->getData())) {
            return new ProductMapping(null, null, null, null, null);
        }

        return new ProductMapping(
            $results->getMagentoProductId(),
            $results->getMagentoVariantId(),
            $results->getGrShopId(),
            $results->getGrProductId(),
            $results->getGrVariantId()
        );
    }


    /**
     * @param ProductMapping $productMapping
     */
    public function saveProductMapping(ProductMapping $productMapping)
    {
        $productMap = $this->objectManager->create(ProductMap::class);
        $productMap->setData([
            'magento_product_id' => $productMapping->getExternalProductId(),
            'magento_variant_id' => $productMapping->getExternalVariantId(),
            'gr_shop_id' => $productMapping->getGrShopId(),
            'gr_product_id' => $productMapping->getGrProductId(),
            'gr_variant_id' => $productMapping->getGrVariantId()
        ]);

        $productMap->save();
    }

    /**
     * @param string $grShopId
     * @param int $externalCartId
     * @param string $grCartId
     */
    public function saveCartMapping($grShopId, $externalCartId, $grCartId)
    {
        $cartMap = $this->objectManager->create(CartMap::class);
        $cartMap->setData([
            'gr_shop_id' => $grShopId,
            'cart_id' => $externalCartId,
            'gr_cart_id' => $grCartId
        ]);

        $cartMap->save();
    }

    /**
     * @param string $grShopId
     * @param int $externalCartId
     * @param string $grCartId
     */
    public function removeCartMapping($grShopId, $externalCartId, $grCartId)
    {
        $cartMap = $this->objectManager->create(CartMap::class);
        $cartMappings = $cartMap->getCollection()
            ->addFieldToFilter('cart_id', $externalCartId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->addFieldToFilter('gr_cart_id', $grCartId);

        foreach($cartMappings as $cartMapping){
            $cartMapping->delete();
        }
    }

    /**
     * @param string $grShopId
     * @param int $externalCartId
     * @return null|int
     */
    public function getGrCartIdFromMapping($grShopId, $externalCartId)
    {
        $cartMap = $this->objectManager->get(CartMap::class);

        $results = $cartMap->getCollection()
            ->addFieldToFilter('cart_id', $externalCartId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getGrCartId() : null;

    }

    /**
     * @param string $grShopId
     * @param int $externalOrderId
     * @return null|int
     */
    public function getGrOrderIdFromMapping($grShopId, $externalOrderId)
    {
        $orderMap = $this->objectManager->get(OrderMap::class);

        $results = $orderMap->getCollection()
            ->addFieldToFilter('order_id', $externalOrderId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getGrOrderId() : null;
    }

    /**
     * @param string $grShopId
     * @param int $externalOrderId
     * @return null|string
     */
    public function getPayloadMd5FromOrderMapping($grShopId, $externalOrderId)
    {
        $orderMap = $this->objectManager->get(OrderMap::class);

        $results = $orderMap->getCollection()
            ->addFieldToFilter('order_id', $externalOrderId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getPayloadMd5() : null;
    }

    /**
     * @param string $grShopId
     * @param int $externalOrderId
     * @param string $grOrderId
     * @param string $payloadMd5
     */
    public function saveOrderMapping($grShopId, $externalOrderId, $grOrderId, $payloadMd5)
    {
        $cartMap = $this->objectManager->create(OrderMap::class);
        $cartMap->setData([
            'gr_shop_id' => $grShopId,
            'order_id' => $externalOrderId,
            'gr_order_id' => $grOrderId,
            'payload_md5' => $payloadMd5
        ]);

        $cartMap->save();
    }

    /**
     * @param Job $job
     * @return null
     */
    public function addJob(Job $job)
    {

    }

    /**
     * @return JobCollection
     */
    public function getJobsToProcess()
    {

    }

    /**
     * @param Job $job
     */
    public function deleteJob(Job $job)
    {

    }


    /**
     * @param int $accountId
     */
    public function markAccountAsInvalid($accountId)
    {
        $this->configWriter->save(
            Config::INVALID_REQUEST_DATE_TIME,
            (new \DateTime('now'))->format('Y-m-d H:i:s'),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * @param $accountId
     */
    public function markAccountAsValid($accountId)
    {
        $this->configWriter->delete(
            Config::INVALID_REQUEST_DATE_TIME,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @param int $accountId
     */
    public function getInvalidAccountFirstOccurrenceDate($accountId)
    {
        return $this->scopeConfig->getValue(Config::INVALID_REQUEST_DATE_TIME);

    }

    /**
     * @param int $accountId
     */
    public function disconnectAccount($accountId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }
}
