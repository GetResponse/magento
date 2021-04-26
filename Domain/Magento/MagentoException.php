<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Exception;

class MagentoException extends Exception
{
    const MISSING_SCOPE_ERROR_CODE = 101;
    const INCORRECT_SCOPE_ERROR_CODE = 102;
    const INVALID_PLUGIN_MODE_ERROR_CODE = 103;
}
