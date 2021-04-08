<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use DateTime;
use Exception;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Model\CartMap;
use GetResponse\GetResponseIntegration\Model\OrderMap;
use GetResponse\GetResponseIntegration\Model\ProductMap;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\ProductMapping\ProductMapping;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class ShareCodeRepository implements DbRepositoryInterface
{
    const ORIGIN_CUSTOM_FIELD_DELETED = 'deleted';

    private $objectManager;
    private $scopeConfig;
    private $configWriter;
    private $cacheManager;
    private $singleRequestCachedOriginCustomFieldId;
    private $magentoStore;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Manager $cacheManager,
        MagentoStore $magentoStore
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->magentoStore = $magentoStore;
    }

    public function getProductMappingByVariantId(
        $grShopId,
        $externalProductId,
        $externalVariantId
    ): ProductMapping {
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

    public function getProductMappingByProductId($grShopId, $externalProductId): ProductMapping
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

    public function removeCartMapping($grShopId, $externalCartId, $grCartId)
    {
        $cartMap = $this->objectManager->create(CartMap::class);
        $cartMappings = $cartMap->getCollection()
            ->addFieldToFilter('cart_id', $externalCartId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->addFieldToFilter('gr_cart_id', $grCartId);

        foreach ($cartMappings as $cartMapping) {
            $cartMapping->delete();
        }
    }

    public function getGrCartIdFromMapping($grShopId, $externalCartId)
    {
        $cartMap = $this->objectManager->get(CartMap::class);

        $results = $cartMap->getCollection()
            ->addFieldToFilter('cart_id', $externalCartId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getGrCartId() : null;
    }

    public function getGrOrderIdFromMapping($grShopId, $externalOrderId)
    {
        $orderMap = $this->objectManager->get(OrderMap::class);

        $results = $orderMap->getCollection()
            ->addFieldToFilter('order_id', $externalOrderId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getGrOrderId() : null;
    }

    public function getPayloadMd5FromOrderMapping($grShopId, $externalOrderId)
    {
        $orderMap = $this->objectManager->get(OrderMap::class);

        $results = $orderMap->getCollection()
            ->addFieldToFilter('order_id', $externalOrderId)
            ->addFieldToFilter('gr_shop_id', $grShopId)
            ->getFirstItem();

        return !empty($results->getData()) ? $results->getPayloadMd5() : null;
    }

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
     * @param int $accountId
     * @throws Exception
     */
    public function markAccountAsInvalid($accountId)
    {
        $this->configWriter->save(
            Config::INVALID_REQUEST_DATE_TIME,
            (new DateTime('now'))->format('Y-m-d H:i:s'),
            $this->getScope(),
            $this->getScopeId()
        );
        $this->cacheManager->clean(['config']);
    }

    public function markAccountAsValid($accountId)
    {
        $this->configWriter->delete(
            Config::INVALID_REQUEST_DATE_TIME,
            $this->getScope(),
            $this->getScopeId()
        );
        $this->cacheManager->clean(['config']);
    }

    public function getInvalidAccountFirstOccurrenceDate($accountId)
    {
        return $this->scopeConfig->getValue(
            Config::INVALID_REQUEST_DATE_TIME,
            $this->getScope(),
            $this->getScopeId()
        );
    }

    public function disconnectAccount($accountId)
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            $this->getScope(),
            $this->getScopeId()
        );
        $this->cacheManager->clean(['config']);
    }

    /**
     * ScopeConfig is build on the beginning of each request.
     * When we change data with specific key within config and after that we ask for the same key in the same request,
     * we will get old value, which is unexpected behaviour.
     * This is the reason why singleRequestCachedOriginCustomFieldId
     * was implemented here, as we want to have always actual value wherever we ask for getOriginCustomFieldId().
     *
     * @return string
     */
    public function getOriginCustomFieldId()
    {
        if (self::ORIGIN_CUSTOM_FIELD_DELETED === $this->singleRequestCachedOriginCustomFieldId) {
            return null;
        }

        if ($this->singleRequestCachedOriginCustomFieldId) {
            return $this->singleRequestCachedOriginCustomFieldId;
        }

        return $this->scopeConfig->getValue(
            Config::CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID,
            $this->getScope(),
            $this->getScopeId()
        );
    }

    public function setOriginCustomFieldId($originCustomFieldId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID,
            $originCustomFieldId,
            $this->getScope(),
            $this->getScopeId()
        );
        $this->cacheManager->clean(['config']);
        $this->singleRequestCachedOriginCustomFieldId = $originCustomFieldId;
    }

    public function clearOriginCustomField()
    {
        $this->configWriter->delete(
            Config::CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID,
            $this->getScope(),
            $this->getScopeId()
        );
        $this->cacheManager->clean(['config']);
        $this->singleRequestCachedOriginCustomFieldId = self::ORIGIN_CUSTOM_FIELD_DELETED;
    }

    private function getScope(): string
    {
        $scopeId = $this->magentoStore->getCurrentScope()->getScopeId();
        return $scopeId === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
    }

    private function getScopeId(): string
    {
        $scopeId = $this->magentoStore->getCurrentScope()->getScopeId();
        return (string) ($scopeId === null ? Store::DEFAULT_STORE_ID : $scopeId);
    }
}
