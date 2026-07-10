<?php

namespace GetResponse\GetResponseIntegration\Helper;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;

class CspNonceProviderFactory
{
    /** @var ProductMetadataInterface */
    protected $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * Handle create.
     */
    public function create(): ?CspNonceProvider
    {
        $version = $this->productMetadata->getVersion();

        if (version_compare($version, '2.4.7', '>=')) {
            $objectManager = ObjectManager::getInstance();
            return $objectManager->get(CspNonceProvider::class);
        }
        return null;
    }
}
