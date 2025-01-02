<?php

namespace GetResponse\GetResponseIntegration\Application\GetResponse\Cart;

class CartIdDecoder
{
    public function decode(string $cartId): int
    {
        return intval(hexdec(substr(md5($cartId), 0, 8)), 16);
    }
}
