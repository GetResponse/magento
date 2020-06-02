<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query;

class GetProduct
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }
}
