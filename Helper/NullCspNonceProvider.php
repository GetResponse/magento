<?php

namespace GetResponse\GetResponseIntegration\Helper;

class NullCspNonceProvider
{
    public function generateNonce(): string
    {
        return "";
    }
}
