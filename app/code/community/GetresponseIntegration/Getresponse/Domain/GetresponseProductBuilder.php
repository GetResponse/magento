<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseProductBuilder
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseProductBuilder
{
    /** @var ApiHelper */
    private $api;

    /** @var string */
    private $shopId;

    /**
     * @param ApiHelper $api
     * @param string $shopId
     */
    public function __construct(ApiHelper $api, $shopId)
    {
        $this->api = $api;
        $this->shopId = $shopId;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $product
     * @return array
     * @throws Exception
     */
    public function createGetresponseProduct(Mage_Sales_Model_Order_Item $product)
    {
        $productMapCollection = Mage::getModel('getresponse/ProductMap')->getCollection();

        /** @var GetresponseIntegration_Getresponse_Model_ProductMap $productMap */
        $productMap = $productMapCollection
            ->addFieldToFilter('entity_id', $product->getProduct()->getId())
            ->addFieldToFilter('gr_shop_id', $this->shopId)
            ->getFirstItem();

        if ($productMap->isEmpty()) {
            $gr_product = $this->createProductInGetResponse($product);

            if (null !== $gr_product['productId']) {
                $productMap->setData([
                    'gr_shop_id' => $this->shopId,
                    'entity_id' => $product->getProduct()->getId(),
                    'gr_product_id' => $gr_product['productId']
                ]);

                $productMap->save();
            }
            return $gr_product;
        }

        return (array) $this->api->getProductById(
            $this->shopId,
            $productMap->getData('gr_product_id')
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Item $product
     * @return array
     */
    private function createProductInGetResponse(Mage_Sales_Model_Order_Item $product)
    {
        $grCategories = $grImages = array();

        foreach ($product->getProduct()->getCategoryIds() as $category_id) {

            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($category_id) ;
            $grCategories[] = array(
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'parentId' => 0,
                'externalId' => $category->getId(),
                'isDefault' => false
            );
        }

        /** @var Varien_Object $image */
        foreach ($product->getProduct()->getMediaGalleryImages() as $image) {

            $grImages[] = array(
                'src' => $image->getData('url'),
                'position' => $image->getData('position')
            );
        }

        $params = array(
            'name' =>$product->getProduct()->getName(),
            'url' => $product->getProduct()->getProductUrl(),
            'categories' => $grCategories,
            'externalId' => $product->getProductId(),
            'variants' => array(
                array(
                    'name' => $product->getProduct()->getName(),
                    'url' => $product->getProduct()->getProductUrl(),
                    'price'=> (float) $product->getPrice(),
                    'priceTax' => (float) $product->getPriceInclTax(),
                    'sku' => $product->getProduct()->getSku(),
                    'quantity' => 1,
                    'description' => $product->getDescription(),
                    'images' => $grImages
                )
            )
        );

        $response = (array) $this->api->addProduct($this->shopId, $params);

        return isset($response['productId']) ? $response : [];
    }
}
