<?php

namespace GetResponse\GetResponseIntegration\Application\GetResponse\Cart;

use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

class CartIdEncryptor
{
    /** @var Encryptor */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     */
    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Handle encrypt.
     *
     * @param string $cartId
     */
    public function encrypt(string $cartId): string
    {
        return $this->encryptor->encrypt($cartId);
    }

    /**
     * Handle decrypt.
     *
     * @param string $cartId
     */
    public function decrypt(string $cartId): string
    {
        return $this->encryptor->decrypt($cartId);
    }
}
