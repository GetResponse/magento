<?php

namespace GetResponse\GetResponseIntegration\Helper;

use Magento\Csp\Helper\CspNonceProvider;

class NullCspNonceProvider
{

    public function generateNonce(): string
    {
        return "";
    }

}
