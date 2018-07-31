<?php

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

/**
 * Class ProductServiceTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Product
 */
class ProductServiceTest extends BaseTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | Repository */
    private $grRepository;

    /** @var ProductService */
    private $sut;


    protected function setUp()
    {
        $this->grRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->sut = new ProductService($this->grRepository);
    }

    /**
     * @test
     */
    public function shouldAdd()
    {
        self::assertTrue($this->sut->add());
    }

}