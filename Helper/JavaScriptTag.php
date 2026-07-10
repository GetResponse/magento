<?php

namespace GetResponse\GetResponseIntegration\Helper;

class JavaScriptTag
{
    /**
     * Generate script tag for const.
     *
     * @param string $const
     * @param string $payload
     * @param ?string $nonceValue
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function generateForConst(string $const, string $payload, ?string $nonceValue): string
    {
        if ($nonceValue === "" || $nonceValue === null) {
            return sprintf('<script type="text/javascript">const %s = %s</script>', $const, $payload);
        }
        return sprintf(
            '<script type="text/javascript" nonce="%s">const %s = %s</script>',
            $nonceValue,
            $const,
            $payload
        );
    }
}
