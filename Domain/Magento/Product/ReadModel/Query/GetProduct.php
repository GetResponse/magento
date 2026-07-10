<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query;

class GetProduct
{
    /** @var mixed */
    private $id;

    /**
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return (int) $this->id;
    }
}
