<?php

namespace GetResponse\GetResponseIntegration\Helper;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\ObjectManager;

class JavaScriptTag
{
    public static function generateForConst(string $const, string $payload, ?string $nonceValue): string
    {
        if ($nonceValue === "" || $nonceValue === null) {
            return sprintf('<script type="text/javascript">const %s = %s</script>', $const, $payload);
        }
        return sprintf('<script type="text/javascript" nonce="%s">const %s = %s</script>', $nonceValue, $const, $payload);
    }
}
