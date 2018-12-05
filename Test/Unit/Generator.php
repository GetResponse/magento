<?php
namespace GetResponse\GetResponseIntegration\Test\Unit;

use GrShareCode\Address\Address;
use GrShareCode\Address\Address as GrAddress;
use GrShareCode\Order\Order;
use GrShareCode\Product\Category\Category;
use GrShareCode\Product\Category\CategoryCollection;
use GrShareCode\Product\Product;
use GrShareCode\Product\ProductsCollection;
use GrShareCode\Product\Variant\Images\Image;
use GrShareCode\Product\Variant\Images\ImagesCollection;
use GrShareCode\Product\Variant\Variant;
use GrShareCode\Product\Variant\VariantsCollection;

/**
 * Class Generator
 * @package GetResponse\GetResponseIntegration\Test\Unit
 */
class Generator
{
    /**
     * @return Order
     */
    public static function createGrOrder($orderId = '')
    {
        return new Order(
            '1000021',
            new ProductsCollection(),
            80.00,
            99.99,
            '',
            'PLN',
            'pending',
            '20000043',
            '',
            20.00,
            '',
            '2018-09-22T12:01:01+0000',
            self::createAddress(),
            self::createAddress()
        );
    }

    /**
     * @return Address
     */
    public static function createAddress()
    {
        return new Address('POL', 'default-address');
    }

    /**
     * @return GrAddress
     */
    public static function createGrAddress()
    {
        return (new Address('POL', 'Poland'))
            ->setFirstName('Adam')
            ->setLastName('Kowalski')
            ->setAddress1('Address number 1')
            ->setAddress2('Address number 2')
            ->setCity('Gdynia')
            ->setZip('81-102')
            ->setProvince('Pomorskie')
            ->setProvinceCode('AASDMEF2')
            ->setPhone('48-123-321-123')
            ->setCompany('GetResponse Company');
    }

    /**
     * @param int $productsCount
     * @param int $variantsCount
     * @return ProductsCollection
     */
    public static function createProductsCollection($productsCount = 1, $variantsCount = 1)
    {

        $products = new ProductsCollection();

        for ($i = 0; $i < $productsCount; $i++) {

            $products->add(
                (new Product(
                    $i + 1,
                    'simple product',
                    self::createProductVariants($variantsCount),
                    self::createCategoriesCollection()
                ))->setUrl('getresponse.com')
            );
        }

        return $products;
    }

    /**
     * @param int $count - number of variants in collection
     * @return VariantsCollection
     */
    public static function createProductVariants($count = 1)
    {
        $variants = new VariantsCollection();

        for ($i = 0; $i < $count; $i++) {
            $imageCollection = new ImagesCollection();
            $imageCollection->add(new Image('https://getresponse.com', 1));

            $productVariant = new Variant(
                $i + 1,
                'simple product',
                9.99,
                12.00,
                'simple-product'
            );

            $productVariant
                ->setQuantity($i + 1)
                ->setUrl('https://getresponse.com')
                ->setDescription('This is description')
                ->setImages($imageCollection);

            $variants->add($productVariant);
        }

        return $variants;
    }

    /**
     * @return CategoryCollection
     */
    public static function createCategoriesCollection()
    {
        $categoryCollection = new CategoryCollection();
        $categoryCollection->add(new Category('t-shirts'));

        return $categoryCollection;
    }

}
