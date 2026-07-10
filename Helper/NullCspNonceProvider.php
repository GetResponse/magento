<?php

namespace GetResponse\GetResponseIntegration\Helper;

class NullCspNonceProvider
{
    /**
     * Handle generate nonce.
     */
    public function generateNonce(): string
    {
        return "";
    }
}
