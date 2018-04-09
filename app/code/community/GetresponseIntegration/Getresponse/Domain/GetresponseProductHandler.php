<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler
{
    /** @var ApiHelper */
    private $api;

    /**
     * @param ApiHelper $api
     */
    public function __construct(ApiHelper $api)
    {
        $this->api = $api;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string                     $storeId
     *
     * @return array
     * @throws Exception
     */
    public function upsertGetresponseProduct(Mage_Catalog_Model_Product $product,
        $storeId
    ) {
        Mage::log('upsertGetresponseProduct', 1, 'getresponse.log');

        $productMapCollection = Mage::getModel('getresponse/ProductMap')
            ->getCollection();

        /** @var GetresponseIntegration_Getresponse_Model_ProductMap $productMap */
        $productMap = $productMapCollection
            ->addFieldToFilter('entity_id', $product->getId())
            ->addFieldToFilter('gr_shop_id', $storeId)
            ->getFirstItem();

        if ($productMap->isEmpty()) {
            $grProduct = $this->createProductInGetResponse($product, $storeId);

            if (null !== $grProduct['productId']) {
                $productMap->setData(
                    array(
                        'gr_shop_id'    => $storeId,
                        'entity_id'     => $product->getId(),
                        'gr_product_id' => $grProduct['productId']
                    )
                );

                $productMap->save();
            }

            return $grProduct;
        }

        return $this->api->getProductById(
            $storeId,
            $productMap->getData('gr_product_id')
        );
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string                     $storeId
     *
     * @return array
     */
    private function createProductInGetResponse(Mage_Catalog_Model_Product $product,
        $storeId
    ) {
        $grCategories = $grImages = array();

        foreach ($product->getCategoryIds() as $category_id) {

            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($category_id);
            $grCategories[] = array(
                'name'       => $category->getName(),
                'url'        => $category->getUrl(),
                'parentId'   => 0,
                'externalId' => $category->getId(),
                'isDefault'  => false
            );
        }

        $mediaGallery = $product->getMediaGalleryImages();

        if (!empty($mediaGallery)) {
            /** @var Varien_Object $image */
            foreach ($mediaGallery as $image) {

                $grImages[] = array(
                    'src'      => $image->getData('url'),
                    'position' => $image->getData('position')
                );
            }
        }

        $params = array(
            'name'       => $product->getName(),
            'url'        => $product->getProductUrl(),
            'categories' => $grCategories,
            'externalId' => $product->getId(),
            'variants'   => array(
                array(
                    'name'        => $product->getName(),
                    'url'         => $product->getProductUrl(),
                    'price'       => (float)$product->getPrice(),
                    'priceTax'    => (float)$product->getFinalPrice(),
                    'sku'         => $product->getSku(),
                    'quantity'    => 1,
                    'description' => $product->getData('short_description'),
                    'images'      => $grImages
                )
            )
        );

        $response = (array)$this->api->addProduct($storeId, $params);
        return isset($response['productId']) ? $response : array();
    }
}
