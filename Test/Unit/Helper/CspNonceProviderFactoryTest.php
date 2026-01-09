<?php

namespace GetResponse\GetResponseIntegration\Test\Unit\Helper;

use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\App\ProductMetadataInterface;

class CspNonceProviderFactoryTest extends BaseTestCase
{
    public function testReturnsNullWhenVersionIsLessThan247()
    {
        $productMetadataMock = $this->createMock(ProductMetadataInterface::class);
        $productMetadataMock->method('getVersion')->willReturn('2.4.6');

        $cspNonceProviderFactory = new CspNonceProviderFactory($productMetadataMock);

        $this->assertNull($cspNonceProviderFactory->create());
    }
}

