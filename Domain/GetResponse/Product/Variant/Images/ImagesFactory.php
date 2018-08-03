<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\Images;

use GrShareCode\Product\Variant\Images\Image;
use GrShareCode\Product\Variant\Images\ImagesCollection;
use Magento\Catalog\Model\Product;

/**
 * Class ImagesFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\Images
 */
class ImagesFactory
{
    /**
     * @param Product $magentoProduct
     * @return ImagesCollection
     */
    public static function fromProduct(Product $magentoProduct)
    {
        $imagesCollection = new ImagesCollection();

        foreach ($magentoProduct->getMediaGalleryImages() as $image) {
            $imagesCollection->add(
                new Image($image->getUrl(), (int)$image->getPosition())
            );
        }

        return $imagesCollection;
    }

}