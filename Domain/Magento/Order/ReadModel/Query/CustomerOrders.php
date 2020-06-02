<?php
declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\Query;

class CustomerOrders
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
