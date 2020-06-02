<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\Images;

use GrShareCode\Product\Variant\Images\Image;
use GrShareCode\Product\Variant\Images\ImagesCollection;
use Magento\Catalog\Model\Product;

class ImagesFactory
{
    public static function fromProduct(Product $magentoProduct): ImagesCollection
    {
        $imagesCollection = new ImagesCollection();

        foreach ($magentoProduct->getMediaGalleryImages() as $image) {
            $imagesCollection->add(new Image($image->getUrl(), (int)$image->getPosition()));
        }

        return $imagesCollection;
    }
}
