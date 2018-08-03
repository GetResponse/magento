<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Model\CartMap;
use GetResponse\GetResponseIntegration\Model\OrderMap;
use GetResponse\GetResponseIntegration\Model\ProductMap;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\Job\Job;
use GrShareCode\Job\JobCollection;
use GrShareCode\ProductMapping\ProductMapping;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class RepositoryForSharedCode
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class RepositoryForSharedCode implements DbRepositoryInterface
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
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
        // TODO: Implement addJob() method.
    }

    /**
     * @return JobCollection
     */
    public function getJobsToProcess()
    {
        // TODO: Implement getJobsToProcess() method.
    }

    /**
     * @param Job $job
     */
    public function deleteJob(Job $job)
    {
        // TODO: Implement deleteJob() method.
    }


}
