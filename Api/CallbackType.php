<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class CallbackType
{
    public const CHECKOUT_CREATE = 'checkouts/create';
    public const CHECKOUT_UPDATE = 'checkouts/update';
    public const CUSTOMER_CREATE = 'customers/create';
    public const CUSTOMER_UPDATE = 'customers/update';
    public const ORDER_CREATE = 'orders/create';
    public const ORDER_UPDATE = 'orders/update';
    public const PRODUCT_CREATE = 'products/create';
    public const PRODUCT_UPDATE = 'products/update';
}
