<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository;

/**
 * Class ProductService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductService
{
    /** @var Repository */
    private $grRepository;

    public function __construct(Repository $grRepository)
    {
        $this->grRepository = $grRepository;
    }

    public function add()
    {
        return true;
    }

}