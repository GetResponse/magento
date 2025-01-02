<?php

namespace GetResponse\GetResponseIntegration\Application\GetResponse\Cart;

class CartIdEncoder
{
    public function encode(int $cartId): string
    {
        return substr(md5(strrev(sprintf('%08x', $cartId))), -8);
    }
}
