<?php

namespace GetResponse\GetResponseIntegration\Application\GetResponse\Cart;

use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

class CartIdEncryptor
{
    /**
     * @var Encryptor $encryptor
     */
    private $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function encrypt(int $cartId): string
    {
        return $this->encryptor->encrypt($cartId);
    }
}
