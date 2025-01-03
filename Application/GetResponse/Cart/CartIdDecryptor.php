<?php

namespace GetResponse\GetResponseIntegration\Application\GetResponse\Cart;

use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

class CartIdDecryptor
{
    /**
     * @var Encryptor $encryptor
     */
    private $encryptor;
    
    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function decrypt(string $cartId): int
    {
        return $this->encryptor->decrypt($cartId);
    }
}
