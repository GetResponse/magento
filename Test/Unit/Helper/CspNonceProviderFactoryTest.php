<?php

namespace GetResponse\GetResponseIntegration\Test\Unit\Helper;

use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\ProductMetadataInterface;
use PHPUnit\Framework\TestCase;

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

