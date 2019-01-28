<?php

/**
 * Class ProductNotFoundException
 */
class ProductNotFoundException extends Exception
{
    /**
     * @return ProductNotFoundException
     */
    public static function buildForInvalidApiKey()
    {
        return new self('API Key not found', self::INVALID_API_KEY);
    }

}