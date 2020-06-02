<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Category\ReadModel\Query;

class CategoryId
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
